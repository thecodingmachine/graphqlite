<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use function is_a;

/**
 * Maps an class extending MyCLabs enums to a GraphQL type
 */
class MyCLabsEnumTypeMapper implements RootTypeMapperInterface
{
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
    {
        return $this->map($type);
    }

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
    {
        return $this->map($type);
    }

    private function map(Type $type): ?EnumType
    {
        if ($type instanceof Object_ && is_a((string) $type->getFqsen(), Enum::class, true)) {
            $enumClass      = (string) $type->getFqsen();
            $consts         = $enumClass::toArray();
            $constInstances = [];
            foreach ($consts as $key => $value) {
                $constInstances[$key] = ['value' => $enumClass::$key()];
            }

            return new EnumType([
                'name' => $type->getFqsen()->getName(),
                'values' => $constInstances,
            ]);
        }

        return null;
    }

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): ?NamedType
    {
        // We cannot map back by name. Hopefully, this is not an issue.
        return null;
    }
}
