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

    public static function createForDecorator(string $class): self
    {
        return new self(sprintf("Input type '%s' cannot be a decorator.", $class));
    }

    public static function createForNotInstantiableClass(string $class): self
    {
        return new self(sprintf("Class '%s' annotated with @Input must be instantiable.", $class));
    }
}
