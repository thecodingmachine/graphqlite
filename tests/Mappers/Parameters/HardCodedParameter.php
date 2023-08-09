<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class HardCodedParameter implements ParameterInterface
{
    public function __construct(private mixed $value = null)
    {
    }

    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $this->value;
    }
}
