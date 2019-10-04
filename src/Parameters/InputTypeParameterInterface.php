<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;

interface InputTypeParameterInterface extends ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     * @param mixed $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info);

    public function getType(): InputType;

    public function hasDefaultValue(): bool;

    /**
     * @return mixed
     */
    public function getDefaultValue();
}
