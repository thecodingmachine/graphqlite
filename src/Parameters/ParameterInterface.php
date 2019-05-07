<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use GraphQL\Type\Definition\ResolveInfo;

/**
 * Instances of ParameterInterface represent a single PHP parameter in a Query/Mutation/Field.
 */
interface ParameterInterface
{
    /**
     * @param object $source
     * @param array<string, mixed> $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($source, $args, $context, ResolveInfo $info);
}
