<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\InputType;

/**
 * Hydrates an object given an array and a GraphQL type.
 */
interface HydratorInterface
{
    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param InputType $type
     * @return object
     */
    public function hydrate(array $data, InputType $type);
}
