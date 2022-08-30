<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Fills a parameter with a default value. Always.
 */
class DefaultValueParameter implements ParameterInterface
{
    /** @var mixed */
    private $defaultValue;

    public function __construct(mixed $defaultValue)
    {
        $this->defaultValue         = $defaultValue;
    }

    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $this->defaultValue;
    }
}
