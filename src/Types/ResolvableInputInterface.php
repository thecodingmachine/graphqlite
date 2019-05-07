<?php

namespace TheCodingMachine\GraphQLite\Types;


use GraphQL\Type\Definition\ResolveInfo;

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
    public function resolve($source, array $args, $context, ResolveInfo $resolveInfo);
}
