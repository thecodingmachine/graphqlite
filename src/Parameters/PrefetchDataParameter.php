<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Typically the first parameter of "self" fields or the second parameter of "external" fields that will be filled with the data fetched from the prefetch method.
 */
class PrefetchDataParameter implements ParameterInterface
{
    private mixed $prefetchedData;

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        // Note: data cannot be known at build time.
        return $this->prefetchedData;
    }

    public function setPrefetchedData(mixed $prefetchedData): void
    {
        $this->prefetchedData = $prefetchedData;
    }
}
