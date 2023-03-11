<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;

use function array_map;
use function assert;
use function gettype;
use function is_object;

class UnionType extends \GraphQL\Type\Definition\UnionType
{
    /** @param array<int,ObjectType&NamedType> $types */
    public function __construct(
        array $types,
        RecursiveTypeMapperInterface $typeMapper,
        NamingStrategyInterface $namingStrategy,
    ) {
        // Make sure all types are object types
        foreach ($types as $type) {
            if (! $type instanceof ObjectType) {
                throw InvalidTypesInUnionException::notObjectType();
            }
        }

        $typeNames = array_map(static fn (ObjectType $type) => $type->name(), $types);
        $name = $namingStrategy->getUnionTypeName($typeNames);

        parent::__construct([
            'name' => $name,
            'types' => $types,
            'resolveType' => static function (mixed $value) use ($typeMapper): ObjectType {
                if (! is_object($value)) {
                    throw new InvalidArgumentException('Expected object for resolveType. Got: "' . gettype($value) . '"');
                }

                $className = $value::class;

                $result = $typeMapper->mapClassToInterfaceOrType($className, null);
                assert($result instanceof ObjectType);

                return $result;
            },
        ]);
    }
}
