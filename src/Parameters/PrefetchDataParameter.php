<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Typically the first parameter of "self" fields or the second parameter of "external" fields that will be filled with the data fetched from the prefetch method.
 */
class PrefetchDataParameter implements ParameterInterface
{
    /** @var mixed */
    private $prefetchedData;

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        // Note: data cannot be known at build time.
        return $this->prefetchedData;
    }

    /**
     * @param mixed $prefetchedData
     */
    public function setPrefetchedData($prefetchedData): void
    {
        $this->prefetchedData = $prefetchedData;
    }
}
