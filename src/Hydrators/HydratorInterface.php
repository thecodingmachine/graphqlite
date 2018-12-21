<?php


namespace TheCodingMachine\GraphQL\Controllers\Hydrators;

use GraphQL\Type\Definition\InputObjectType;

/**
 * Hydrates an object given an array and a GraphQL type.
 */
interface HydratorInterface
{
    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return object
     */
    public function hydrate(array $data, InputObjectType $type);
}
