<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Typically the first parameter of "external" fields that will be filled with the Source object.
 */
class SourceParameter implements ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $source;
    }
}
