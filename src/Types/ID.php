<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;

use function is_object;
use function is_scalar;
use function method_exists;

/**
 * A class that maps to the GraphQL ID type.
 */
class ID
{
    /**
     * Note: if $value is an object, it has a __toString method on it.
     */
    public function __construct(private bool|float|int|string|object $value)
    {
        if (! is_scalar($value) && (! is_object($value) || ! method_exists($value, '__toString'))) {
            throw new InvalidArgumentException('ID constructor cannot be passed a non scalar value.');
        }
    }

    /**
     * Note: if returned value is an object, it has a __toString method on it.
     */
    public function val(): bool|float|int|string|object
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
