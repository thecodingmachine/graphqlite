<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use RuntimeException;
use function sprintf;

class FailedResolvingInputType extends RuntimeException
{
    public static function createForMissingConstructorParameter(string $class, string $parameter): self
    {
        return new self(sprintf("Parameter '%s' is missing for class '%s' constructor. It should be mapped as required field.", $parameter, $class));
    }

    public static function createForDecorator(): self
    {
        return new self('Input type cannot be a decorator');
    }
}
