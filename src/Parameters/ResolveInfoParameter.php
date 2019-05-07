<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use GraphQL\Type\Definition\ResolveInfo;

/**
 * A parameter type-hinted to ResolveInfo
 */
class ResolveInfoParameter implements ParameterInterface
{
    /**
     * @param object $source
     * @param array<string, mixed> $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($source, $args, $context, ResolveInfo $info)
    {
        return $info;
    }
}
