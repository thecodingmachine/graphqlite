<?php

namespace TheCodingMachine\GraphQLite;

use RuntimeException;

class FailedResolvingInputType extends RuntimeException
{

    /**
     * @param string $class
     * @param string $parameter
     *
     * @return self
     */
    public static function createForMissingConstructorParameter(string $class, string $parameter): self
    {
        return new self(sprintf("Parameter '%s' is missing for class '%s' constructor. It should be mapped as required field.", $parameter, $class));
    }

    /**
     * @return self
     */
    public static function createForDecorator(): self
    {
        return new self('Input type cannot be a decorator');
    }
}
