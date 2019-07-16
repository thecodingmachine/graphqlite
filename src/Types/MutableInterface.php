<?php


namespace TheCodingMachine\GraphQLite\Types;

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
}
