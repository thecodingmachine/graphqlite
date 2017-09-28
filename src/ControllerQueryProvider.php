<?php


namespace TheCodingMachine\GraphQL\Controllers;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\Types\Integer;
use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\BooleanType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\Scalar\FloatType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\TypeInterface;
use Youshido\GraphQL\Type\Union\UnionType;

/**
 * A query provider that looks for queries in a "controller"
 */
class ControllerQueryProvider implements QueryProviderInterface
{
    /**
     * @var object
     */
    private $controller;
    /**
     * @var Reader
     */
    private $annotationReader;
    /**
     * @var TypeMapperInterface
     */
    private $typeMapper;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;
    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;

    /**
     * @param object $controller
     */
    public function __construct($controller, Reader $annotationReader, TypeMapperInterface $typeMapper, HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService, AuthorizationServiceInterface $authorizationService)
    {
        $this->controller = $controller;
        $this->annotationReader = $annotationReader;
        $this->typeMapper = $typeMapper;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @return Field[]
     */
    public function getQueries(): array
    {
        return $this->getFieldsByAnnotations(Query::class);
    }

    /**
     * @return Field[]
     */
    public function getMutations(): array
    {
        return $this->getFieldsByAnnotations(Mutation::class);
    }

    /**
     * @return Field[]
     */
    private function getFieldsByAnnotations(string $annotationName): array
    {
        $refClass = ReflectionClass::createFromInstance($this->controller);

        $queryList = [];

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($refClass->getMethods() as $refMethod) {
            $standardPhpMethod = new \ReflectionMethod(get_class($this->controller), $refMethod->getName());
            // First, let's check the "Query" annotation
            $queryAnnotation = $this->annotationReader->getMethodAnnotation($standardPhpMethod, $annotationName);

            if ($queryAnnotation !== null) {
                if (!$this->isAuthorized($standardPhpMethod)) {
                    continue;
                }

                $methodName = $refMethod->getName();

                $args = $this->mapParameters($refMethod, $standardPhpMethod);

                $phpdocType = $typeResolver->resolve((string) $refMethod->getReturnType());

                try {
                    $type = $this->mapType($phpdocType, $refMethod->getDocBlockReturnTypes(), $standardPhpMethod->getReturnType()->allowsNull(), false);
                } catch (TypeMappingException $e) {
                    throw TypeMappingException::wrapWithReturnInfo($e, $refMethod);
                }
                $queryList[] = new QueryField($methodName, $type, $args, [$this->controller, $methodName], $this->hydrator);
            }
        }

        return $queryList;
    }

    /**
     * Checks the @Logged and @Right annotations.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @return bool
     */
    private function isAuthorized(\ReflectionMethod $reflectionMethod) : bool
    {
        $loggedAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, Logged::class);

        if ($loggedAnnotation !== null && !$this->authenticationService->isLogged()) {
            return false;
        }

        $rightAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, Right::class);
        /** @var $rightAnnotation Right */

        if ($rightAnnotation !== null && !$this->authorizationService->isAllowed($rightAnnotation->getName())) {
            return false;
        }

        return true;
    }

    /**
     * Note: there is a bug in $refMethod->allowsNull that forces us to use $standardRefMethod->allowsNull instead.
     *
     * @param ReflectionMethod $refMethod
     * @param \ReflectionMethod $standardRefMethod
     * @return array
     * @throws MissingTypeHintException
     */
    private function mapParameters(ReflectionMethod $refMethod, \ReflectionMethod $standardRefMethod)
    {
        $args = [];

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($standardRefMethod->getParameters() as $standardParameter) {
            $allowsNull = $standardParameter->allowsNull();
            $parameter = $refMethod->getParameter($standardParameter->getName());

            $type = (string) $parameter->getType();
            if ($type === '') {
                throw MissingTypeHintException::missingTypeHint($parameter);
            }
            $phpdocType = $typeResolver->resolve($type);

            try {
                $arr = [
                    'type' => $this->mapType($phpdocType, $parameter->getDocBlockTypes(), $allowsNull, true),
                ];
            } catch (TypeMappingException $e) {
                throw TypeMappingException::wrapWithParamInfo($e, $parameter);
            }

            if ($standardParameter->allowsNull()) {
                $arr['default'] = null;
            }
            if ($standardParameter->isDefaultValueAvailable()) {
                $arr['default'] = $standardParameter->getDefaultValue();
            }

            $args[$parameter->getName()] = $arr;
        }

        return $args;
    }

    /**
     * @param Type $type
     * @param Type[] $docBlockTypes
     * @return TypeInterface
     */
    private function mapType(Type $type, array $docBlockTypes, bool $isNullable, bool $mapToInputType): TypeInterface
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Mixed_) {
            if (!$isNullable) {
                // Let's check a "null" value in the docblock
                $isNullable = $this->isNullable($docBlockTypes);
            }
            $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockTypes);
            if (empty($filteredDocBlockTypes)) {
                throw TypeMappingException::createFromType($type);
            } elseif (count($filteredDocBlockTypes) === 1) {
                $graphQlType = $this->toGraphQlType($filteredDocBlockTypes[0], $mapToInputType);
            } else {
                throw new GraphQLException('Union types are not supported (yet)');
                //$graphQlTypes = array_map([$this, 'toGraphQlType'], $filteredDocBlockTypes);
                //$$graphQlType = new UnionType($graphQlTypes);
            }
        } else {
            $graphQlType = $this->toGraphQlType($type, $mapToInputType);
        }

        if (!$isNullable) {
            $graphQlType = new NonNullType($graphQlType);
        }

        return $graphQlType;
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @param Type $type
     * @param bool $mapToInputType
     * @return TypeInterface
     */
    private function toGraphQlType(Type $type, bool $mapToInputType): TypeInterface
    {
        if ($type instanceof Integer) {
            return new IntType();
        } elseif ($type instanceof String_) {
            return new StringType();
        } elseif ($type instanceof Boolean) {
            return new BooleanType();
        } elseif ($type instanceof Float_) {
            return new FloatType();
        } elseif ($type instanceof Object_) {
            $fqcn = (string) $type->getFqsen();
            if ($fqcn === '\\DateTimeImmutable' || $fqcn === '\\DateTimeInterface') {
                return new DateTimeType();
            } elseif ($fqcn === '\\DateTime') {
                throw new GraphQLException('Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
            }

            $className = ltrim($type->getFqsen(), '\\');
            if ($mapToInputType) {
                return $this->typeMapper->mapClassToInputType($className);
            } else {
                return $this->typeMapper->mapClassToType($className);
            }
        } elseif ($type instanceof Array_) {
            return new ListType(new NonNullType($this->toGraphQlType($type->getValueType(), $mapToInputType)));
        } else {
            throw new GraphQLException("Don't know how to handle type ".((string) $type));
        }
    }

    /**
     * Removes "null" from the list of types.
     *
     * @param Type[] $docBlockTypeHints
     * @return array
     */
    private function typesWithoutNullable(array $docBlockTypeHints): array
    {
        return array_filter($docBlockTypeHints, function ($item) {
            return !$item instanceof Null_;
        });
    }

    /**
     * @param Type[] $docBlockTypeHints
     * @return bool
     */
    private function isNullable(array $docBlockTypeHints): bool
    {
        foreach ($docBlockTypeHints as $docBlockTypeHint) {
            if ($docBlockTypeHint instanceof Null_) {
                return true;
            }
        }
        return false;
    }
}
