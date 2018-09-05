<?php


namespace TheCodingMachine\GraphQL\Controllers;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Psr\Container\ContainerInterface;
use \ReflectionClass;
use \ReflectionMethod;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\Types\Integer;
use TheCodingMachine\GraphQL\Controllers\Annotations\AbstractRequest;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Reflection\CommentParser;
use TheCodingMachine\GraphQL\Controllers\Registry\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
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
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param object $controller
     */
    public function __construct($controller, RegistryInterface $registry)
    {
        $this->controller = $controller;
        $this->annotationReader = $registry->getAnnotationReader();
        $this->typeMapper = $registry->getTypeMapper();
        $this->hydrator = $registry->getHydrator();
        $this->authenticationService = $registry->getAuthenticationService();
        $this->authorizationService = $registry->getAuthorizationService();
        $this->registry = $registry;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        return $this->getFieldsByAnnotations(Query::class, false);
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        return $this->getFieldsByAnnotations(Mutation::class, false);
    }

    /**
     * @return QueryField[]
     */
    public function getFields(): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations(Annotations\Field::class, true);
        $sourceFields = $this->getSourceFields();

        return array_merge($fieldAnnotations, $sourceFields);
    }

    /**
     * @param string $annotationName
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field, false for @Query and @Mutation
     * @return QueryField[]
     * @throws \ReflectionException
     */
    private function getFieldsByAnnotations(string $annotationName, bool $injectSource): array
    {
        $refClass = new \ReflectionClass($this->controller);
        $docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($refClass);

        //$refClass = ReflectionClass::createFromInstance($this->controller);

        $queryList = [];

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($refClass->getMethods() as $refMethod) {
            // First, let's check the "Query" or "Mutation" or "Field" annotation
            $queryAnnotation = $this->annotationReader->getMethodAnnotation($refMethod, $annotationName);
            /* @var $queryAnnotation AbstractRequest */

            if ($queryAnnotation !== null) {
                if (!$this->isAuthorized($refMethod)) {
                    continue;
                }

                $docComment = $refMethod->getDocComment() ?: '/** */';
                $docBlockObj = $docBlockFactory->create($docComment, $context);

                // TODO: change CommentParser to use $docBlockObj methods instead.
                $docBlock = new CommentParser($refMethod->getDocComment());

                $methodName = $refMethod->getName();
                $name = $queryAnnotation->getName() ?: $methodName;

                $args = $this->mapParameters($refMethod, $docBlockObj);

                $phpdocType = $typeResolver->resolve((string) $refMethod->getReturnType());

                if ($queryAnnotation->getReturnType()) {
                    $type = $this->registry->get($queryAnnotation->getReturnType());
                } else {
                    $docBlockReturnType = $this->getDocBlocReturnType($docBlockObj, $refMethod);

                    try {
                        $type = $this->mapType($phpdocType, $docBlockReturnType, $refMethod->getReturnType()->allowsNull(), false);
                    } catch (TypeMappingException $e) {
                        throw TypeMappingException::wrapWithReturnInfo($e, $refMethod);
                    }
                }

                //$sourceType = null;
                if ($injectSource === true) {
                    /*$sourceArr = */\array_shift($args);
                    // Security check: if the first parameter of the correct type?
                    //$sourceType = $sourceArr['type'];
                    /* @var $sourceType TypeInterface */
                    // TODO
                }
                $queryList[] = new QueryField($name, $type, $args, [$this->controller, $methodName], null, $this->hydrator, $docBlock->getComment(), $injectSource);
            }
        }

        return $queryList;
    }

    private function getDocBlocReturnType(DocBlock $docBlock, \ReflectionMethod $refMethod): ?Type
    {
        /** @var Return_[] $returnTypeTags */
        $returnTypeTags = $docBlock->getTagsByName('return');
        if (count($returnTypeTags) > 1) {
            // TODO: clean exception
            throw new \Exception('Method '.$refMethod->getDeclaringClass()->getName().'::'.$refMethod->getName().' has several @return annotations.');
        }
        $docBlockReturnType = null;
        if (isset($returnTypeTags[0])) {
            $docBlockReturnType = $returnTypeTags[0]->getType();
        }
        return $docBlockReturnType;
    }

    /**
     * @return QueryField[]
     */
    private function getSourceFields(): array
    {
        $refClass = new \ReflectionClass($this->controller);
        $docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $contextFactory = new ContextFactory();
        $context = $contextFactory->createFromReflector($refClass);

        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->annotationReader->getClassAnnotations($refClass);
        $sourceFields = \array_filter($sourceFields, function($annotation): bool {
            return $annotation instanceof SourceField;
        });

        if (empty($sourceFields)) {
            return [];
        }

        /** @var \TheCodingMachine\GraphQL\Controllers\Annotations\Type $typeField */
        $typeField = $this->annotationReader->getClassAnnotation($refClass, \TheCodingMachine\GraphQL\Controllers\Annotations\Type::class);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException();
        }

        $objectClass = $typeField->getClass();
        $objectRefClass = new \ReflectionClass($objectClass);

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($sourceFields as $sourceField) {
            // Ignore the field if we must be logged.
            if ($sourceField->isLogged() && !$this->authenticationService->isLogged()) {
                continue;
            }

            $right = $sourceField->getRight();
            if ($right !== null && !$this->authorizationService->isAllowed($right->getName())) {
                continue;
            }

            try {
                $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getName());
            } catch (FieldNotFoundException $e) {
                throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
            }

            $docBlock = new CommentParser($refMethod->getDocComment());

            $methodName = $refMethod->getName();

            $docComment = $refMethod->getDocComment() ?: '/** */';
            $docBlockObj = $docBlockFactory->create($docComment, $context);

            $args = $this->mapParameters($refMethod, $docBlockObj);

            $phpdocType = $typeResolver->resolve((string) $refMethod->getReturnType());

            if ($sourceField->getReturnType()) {
                $type = $this->registry->get($sourceField->getReturnType());
            } else {
                $docBlockReturnType = $this->getDocBlocReturnType($docBlockObj, $refMethod);

                try {
                    $type = $this->mapType($phpdocType, $docBlockReturnType, $refMethod->getReturnType()->allowsNull(), false);
                } catch (TypeMappingException $e) {
                    throw TypeMappingException::wrapWithReturnInfo($e, $refMethod);
                }
            }

            $queryList[] = new QueryField($sourceField->getName(), $type, $args, null, $methodName, $this->hydrator, $docBlock->getComment(), false);

        }
        return $queryList;
    }

    private function getMethodFromPropertyName(\ReflectionClass $reflectionClass, string $propertyName): \ReflectionMethod
    {
        $upperCasePropertyName = \ucfirst($propertyName);
        if ($reflectionClass->hasMethod('get'.$upperCasePropertyName)) {
            $methodName = 'get'.$upperCasePropertyName;
        } elseif ($reflectionClass->hasMethod('is'.$upperCasePropertyName)) {
            $methodName = 'is'.$upperCasePropertyName;
        } else {
            throw FieldNotFoundException::missingField($reflectionClass->getName(), $propertyName);
        }

        return $reflectionClass->getMethod($methodName);
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
     * @param \ReflectionMethod $refMethod
     * @return array[] An array of ['type'=>TypeInterface, 'default'=>val]
     * @throws MissingTypeHintException
     */
    private function mapParameters(\ReflectionMethod $refMethod, DocBlock $docBlock): array
    {
        $args = [];

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($refMethod->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            $allowsNull = $parameterType === null ? true : $parameterType->allowsNull();

            $type = (string) $parameterType;
            if ($type === '') {
                throw MissingTypeHintException::missingTypeHint($parameter);
            }
            $phpdocType = $typeResolver->resolve($type);

            /** @var DocBlock\Tags\Param[] $paramTags */
            $paramTags = $docBlock->getTagsByName('param');
            $docBlockType = null;
            foreach ($paramTags as $paramTag) {
                if ($paramTag->getVariableName() === $parameter->getName()) {
                    $docBlockType = $paramTag->getType();
                    break;
                }
            }

            try {
                $arr = [
                    'type' => $this->mapType($phpdocType, $docBlockType, $allowsNull || $parameter->isDefaultValueAvailable(), true),
                ];
            } catch (TypeMappingException $e) {
                throw TypeMappingException::wrapWithParamInfo($e, $parameter);
            }

            if ($parameter->allowsNull()) {
                $arr['default'] = null;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $arr['default'] = $parameter->getDefaultValue();
            }

            $args[$parameter->getName()] = $arr;
        }

        return $args;
    }

    /**
     * @param Type $type
     * @param Type|null $docBlockType
     * @return TypeInterface
     */
    private function mapType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType): TypeInterface
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Mixed_) {
            if ($docBlockType === null) {
                throw TypeMappingException::createFromType($type);
            }
            if (!$isNullable) {
                // Let's check a "null" value in the docblock
                $isNullable = $this->isNullable($docBlockType);
            }
            $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockType);
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
            throw TypeMappingException::createFromType($type);
        }
    }

    /**
     * Removes "null" from the type (if it is compound). Return an array of types (not a Compound type).
     *
     * @param Type $docBlockTypeHint
     * @return array
     */
    private function typesWithoutNullable(Type $docBlockTypeHint): array
    {
        if ($docBlockTypeHint instanceof Compound) {
            $docBlockTypeHints = \iterator_to_array($docBlockTypeHint);
        } else {
            $docBlockTypeHints = [$docBlockTypeHint];
        }
        return array_filter($docBlockTypeHints, function ($item) {
            return !$item instanceof Null_;
        });
    }

    /**
     * @param Type $docBlockTypeHint
     * @return bool
     */
    private function isNullable(Type $docBlockTypeHint): bool
    {
        if ($docBlockTypeHint instanceof Null_) {
            return true;
        }
        if ($docBlockTypeHint instanceof Compound) {
            foreach ($docBlockTypeHint as $type) {
                if ($type instanceof Null_) {
                    return true;
                }
            }
        }

        return false;
    }
}
