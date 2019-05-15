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
    /** @var bool|float|int|string */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        if (! is_scalar($value) && (! is_object($value) || ! method_exists($value, '__toString'))) {
            throw new InvalidArgumentException('ID constructor cannot be passed a non scalar value.');
        }
        $this->value = $value;
    }

    /**
     * @return bool|float|int|string
     */
    public function val()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
