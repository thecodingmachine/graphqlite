<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations\Exceptions;

use BadMethodCallException;

class IncompatibleAnnotationsException extends BadMethodCallException
{
    public static function cannotUseFailWithAndHide(): self
    {
        return new self('You cannot use "FailWith" and "HideIfUnauthorized" annotations in the same method. These annotations are mutually exclusive.');
    }
}
