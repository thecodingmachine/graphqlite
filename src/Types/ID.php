<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;

use function is_bool;
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
    public function __construct(private readonly bool|float|int|string|object $value)
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
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
        if (is_bool($this->value)) {
            return $this->value === true ? '1' : '0';
        }

        if (is_scalar($this->value)) {
            return (string) $this->value;
        }

        return $this->value->__toString();
    }
}
