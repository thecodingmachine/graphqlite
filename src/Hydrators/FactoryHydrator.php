<?php


namespace TheCodingMachine\GraphQL\Controllers\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use TheCodingMachine\GraphQL\Controllers\GraphQLException;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Types\ResolvableInputObjectType;

/**
 * Hydrates input types based on the Factory annotation.
 */
class FactoryHydrator implements HydratorInterface
{

    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return object
     */
    public function hydrate(array $data, InputObjectType $type)
    {
        if ($type instanceof ResolvableInputObjectType) {
            return $type->resolve($data);
        }
        throw new GraphQLException('Cannot hydrate type '.$type->name);
    }
}
