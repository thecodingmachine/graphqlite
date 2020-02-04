<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Types\MyCLabsEnumType;
use function is_a;

/**
 * Maps an class extending MyCLabs enums to a GraphQL type
 */
class MyCLabsEnumTypeMapper implements RootTypeMapperInterface
{
    /** @var array<class-string<object>, EnumType> */
    private $cache = [];
    /** @var array<string, EnumType> */
    private $cacheByName = [];
    /** @var RootTypeMapperInterface */
    private $next;
    /** @var AnnotationReader */
    private $annotationReader;

    public function __construct(RootTypeMapperInterface $next, AnnotationReader $annotationReader)
    {
        $this->next = $next;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        $result = $this->map($type);
        if ($result === null) {
            return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }

        return $result;
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        $result = $this->map($type);
        if ($result === null) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }

        return $result;
    }

    private function map(Type $type): ?EnumType
    {
        if (! $type instanceof Object_) {
            return null;
        }
        $fqsen = $type->getFqsen();
        if ($fqsen === null) {
            return null;
        }
        /**
         * @var class-string<object>
         */
        $enumClass = (string) $fqsen;

        return $this->mapByClassName($enumClass);
    }

    /**
     * @param class-string<object> $enumClass
     */
    private function mapByClassName(string $enumClass): ?EnumType
    {
        if (! is_a($enumClass, Enum::class, true)) {
            return null;
        }
        if (isset($this->cache[$enumClass])) {
            return $this->cache[$enumClass];
        }

        $type = new MyCLabsEnumType($enumClass, $this->getTypeName($enumClass));
        return $this->cacheByName[$type->name] = $this->cache[$enumClass] = $type;
    }

    /**
     * @param class-string<Enum> $enumClass
     */
    private function getTypeName(string $enumClass): string
    {
        $refClass = new ReflectionClass($enumClass);
        $enumType = $this->annotationReader->getEnumTypeAnnotation($refClass);
        if ($enumType !== null) {
            $name = $enumType->getName();
            if ($name !== null) {
                return $name;
            }
        }
        return $refClass->getShortName();
    }

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): NamedType
    {
        // This is a hack to make sure "$schema->assertValid()" returns true.
        // The mapNameToType will fail if the mapByClassName method was not called before.
        // This is actually not an issue in real life scenarios where enum types are never queried by type name.
        if (isset($this->cacheByName[$typeName])) {
            return $this->cacheByName[$typeName];
        }
        /*if (strpos($typeName, 'MyCLabsEnum_') === 0) {
            $className = str_replace('__', '\\', substr($typeName, 12));

            $type = $this->mapByClassName($className);
            if ($type !== null) {
                return $type;
            }
        }*/

        return $this->next->mapNameToType($typeName);
    }
}
