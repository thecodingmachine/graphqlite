<?php


namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;

/**
 * Hydrates an object given an array and a GraphQL type.
 */
interface HydratorInterface
{
    /**
     * Whether this hydrator can hydrate the passed data.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return bool
     */
    public function canHydrate(array $data, InputObjectType $type): bool;

    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return object
     * @throws CannotHydrateException
     */
    public function hydrate(array $data, InputObjectType $type);
}
