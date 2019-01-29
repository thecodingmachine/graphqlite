<?php


namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType;

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
     * @throws CannotHydrateException
     */
    public function hydrate(array $data, InputObjectType $type)
    {
        if ($type instanceof ResolvableInputObjectType) {
            return $type->resolve($data);
        }
        throw CannotHydrateException::createForInputType($type->name);
    }

    /**
     * Whether this hydrate can hydrate the passed data.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return bool
     */
    public function canHydrate(array $data, InputObjectType $type): bool
    {
        return $type instanceof ResolvableInputObjectType;
    }
}
