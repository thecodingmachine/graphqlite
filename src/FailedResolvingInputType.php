<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ArgumentCountError;
use RuntimeException;

use function sprintf;

class FailedResolvingInputType extends RuntimeException
{
    public static function createForMissingConstructorParameter(ArgumentCountError $original): self
    {
        return new self(sprintf('%s. It should be mapped as required field.', $original->getMessage()), previous: $original);
    }

    public static function createForDecorator(string $class): self
    {
        return new self(sprintf("Input type '%s' cannot be a decorator.", $class));
    }

    public static function createForNotInstantiableClass(string $class): self
    {
        return new self(sprintf("Class '%s' annotated with @Input must be instantiable.", $class));
    }
}
