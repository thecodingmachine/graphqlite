<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;

/**
 * GraphQL objects or interfaces that can be muted.
 */
interface MutableInterface
{
    // In pending state, we can still add fields.
    public const STATUS_PENDING = 'pending';
    public const STATUS_FROZEN  = 'frozen';

    public function freeze(): void;

    public function getStatus(): string;

    public function addFields(callable $fields): void;

    /**
     * @return FieldDefinition[]
     *
     * @throws InvariantViolation
     */
    public function getFields(): array;
}
