<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use GraphQL\Type\Definition\ResolveInfo;

/**
 * Typically the first parameter of "external" fields that will be filled with the Source object.
 */
class SourceParameter implements ParameterInterface
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
        return $source;
    }
}
