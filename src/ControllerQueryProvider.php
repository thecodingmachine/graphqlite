<?php


namespace TheCodingMachine\GraphQL\Controllers;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Doctrine\Common\Annotations\Reader;
use phpDocumentor\Reflection\Types\Integer;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
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
     * @param object $controller
     */
    public function __construct($controller, Reader $annotationReader, TypeMapperInterface $typeMapper, HydratorInterface $hydrator)
    {
        $this->controller = $controller;
        $this->annotationReader = $annotationReader;
        $this->typeMapper = $typeMapper;
        $this->hydrator = $hydrator;
    }

    /**
     * @return Field[]
     */
    public function getQueries(): array
    {
        $refClass = ReflectionClass::createFromInstance($this->controller);

        $queryList = [];

        foreach ($refClass->getMethods() as $refMethod) {
            $standardPhpMethod = new \ReflectionMethod(get_class($this->controller), $refMethod->getName());
            // First, let's check the "Query" annotation
            $queryAnnotation = $this->annotationReader->getMethodAnnotation($standardPhpMethod, Query::class);
            if ($queryAnnotation !== null) {
                $methodName = $refMethod->getName();

                $args = $this->mapParameters($refMethod, $standardPhpMethod);

                $type = $this->mapType($refMethod->getReturnType()->getTypeObject(), $refMethod->getDocBlockReturnTypes(), $standardPhpMethod->getReturnType()->allowsNull());

                $queryList[] = new QueryField($methodName, $type, $args, [$this->controller, $methodName], $this->hydrator);
            }
        }

        return $queryList;
    }

    /**
     * Note: there is a bug in $refMethod->allowsNull that forces us to use $standardRefMethod->allowsNull instead.
     *
     * @param ReflectionMethod $refMethod
     * @param \ReflectionMethod $standardRefMethod
     * @return array
     */
    private function mapParameters(ReflectionMethod $refMethod, \ReflectionMethod $standardRefMethod)
    {
        $args = [];
        foreach ($standardRefMethod->getParameters() as $standardParameter) {
            $allowsNull = $standardParameter->allowsNull();
            $parameter = $refMethod->getParameter($standardParameter->getName());
            $args[$parameter->getName()] = $this->mapType($parameter->getTypeHint(), $parameter->getDocBlockTypes(), $allowsNull);
        }

        return $args;
    }

    /**
     * @param Type $type
     * @param Type[] $docBlockTypes
     * @return TypeInterface
     */
    private function mapType(Type $type, array $docBlockTypes, bool $isNullable): TypeInterface
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Mixed) {
            if (!$isNullable) {
                // Let's check a "null" value in the docblock
                $isNullable = $this->isNullable($docBlockTypes);
            }
            $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockTypes);
            if (empty($filteredDocBlockTypes)) {
                // TODO: improve error message
                throw new GraphQLException("Don't know how to handle type ".((string) $type));
            } elseif (count($filteredDocBlockTypes) === 1) {
                $graphQlType = $this->toGraphQlType($filteredDocBlockTypes[0]);
            } else {
                throw new GraphQLException('Union types are not supported (yet)');
                //$graphQlTypes = array_map([$this, 'toGraphQlType'], $filteredDocBlockTypes);
                //$$graphQlType = new UnionType($graphQlTypes);
            }
        } else {
            $graphQlType = $this->toGraphQlType($type);
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
     * @return TypeInterface
     */
    private function toGraphQlType(Type $type): TypeInterface
    {
        if ($type instanceof Integer) {
            return new IntType();
        } elseif ($type instanceof String_) {
            return new StringType();
        } elseif ($type instanceof Object_) {
            return $this->typeMapper->mapClassToType(ltrim($type->getFqsen(), '\\'));
        } elseif ($type instanceof Array_) {
            return new ListType(new NonNullType($this->toGraphQlType($type->getValueType())));
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
        return array_filter($docBlockTypeHints, function($item) {
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
