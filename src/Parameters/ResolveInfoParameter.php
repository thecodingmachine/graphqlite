<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * A parameter type-hinted to ResolveInfo
 */
class ResolveInfoParameter implements ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        return $info;
    }
}
