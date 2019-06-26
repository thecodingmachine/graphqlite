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

    /**
     * @param mixed $defaultValue
     */
    public function __construct($defaultValue)
    {
        $this->defaultValue         = $defaultValue;
    }

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        return $this->defaultValue;
    }
}
