<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;

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
            $enumClass = (string) $type->getFqsen();
            $consts = $enumClass::toArray();
            $constInstances = [];
            foreach ($consts as $key => $value) {
                $constInstances[$value] = ['value' => $enumClass::$key()];
            }
            return new EnumType([
                'name' => $type->getFqsen()->getName(),
                'description' => 'One of the films in the Star Wars Trilogy',
                'values' => $constInstances
            ]);
        }
        return null;
    }
}
