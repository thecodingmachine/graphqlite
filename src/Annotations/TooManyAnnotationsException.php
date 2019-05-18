<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use LogicException;

class TooManyAnnotationsException extends LogicException
{
    public static function forClass(string $className): self
    {
        return new self('Expected at most one annotation @"' . $className . '"');
    }
}
