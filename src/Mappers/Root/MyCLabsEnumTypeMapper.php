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
use ReflectionMethod;
use function is_a;
use function str_replace;
use function strpos;
use function substr;

/**
 * Maps an class extending MyCLabs enums to a GraphQL type
 */
class MyCLabsEnumTypeMapper implements RootTypeMapperInterface
{
    /** @var array<string, EnumType> */
    private $cache = [];
    /** @var RootTypeMapperInterface */
    private $next;

    public function __construct(RootTypeMapperInterface $next)
    {
        $this->next = $next;
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
        $enumClass = (string) $fqsen;

        return $this->mapByClassName($enumClass);
    }

    private function mapByClassName(string $enumClass): ?EnumType
    {
        if (! is_a($enumClass, Enum::class, true)) {
            return null;
        }
        if (isset($this->cache[$enumClass])) {
            return $this->cache[$enumClass];
        }

        $consts         = $enumClass::toArray();
        $constInstances = [];
        foreach ($consts as $key => $value) {
            $constInstances[$key] = ['value' => $enumClass::$key()];
        }

        return $this->cache[$enumClass] = new EnumType([
            'name' => 'MyCLabsEnum_' . str_replace('\\', '__', $enumClass),
            'values' => $constInstances,
        ]);
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
        if (strpos($typeName, 'MyCLabsEnum_') === 0) {
            $className = str_replace('__', '\\', substr($typeName, 12));

            $type = $this->mapByClassName($className);
            if ($type !== null) {
                return $type;
            }
        }

        return $this->next->mapNameToType($typeName);
    }
}
