<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface InputTypeParameterInterface extends ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed;

    public function getType(): InputType;

    public function hasDefaultValue(): bool;

    public function getDefaultValue(): mixed;
}
