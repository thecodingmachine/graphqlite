<?php

namespace TheCodingMachine\GraphQLite\Types;


/**
 * A GraphQL input object that can be resolved
 */
interface ResolvableInputInterface
{
    /**
     * Resolves the arguments into an object.
     *
     * @param array $args
     * @return object
     */
    public function resolve(array $args);
}
