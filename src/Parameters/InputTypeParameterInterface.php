<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

/** @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html */
interface InputTypeParameterInterface extends ParameterInterface
{
    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): mixed;

    public function getType(): InputType&Type;

    public function hasDefaultValue(): bool;

    public function getDefaultValue(): mixed;

    public function getName(): string;

    public function getDescription(): string;
}
