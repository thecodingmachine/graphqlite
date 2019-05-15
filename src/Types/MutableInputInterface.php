<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InputType;

/**
 * An input object type to which we can add fields after instantiation.
 */
interface MutableInputInterface extends InputType
{
    public function freeze(): void;

    public function getStatus(): string;

    public function addFields(callable $fields): void;
}
