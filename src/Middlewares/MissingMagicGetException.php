<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use RuntimeException;

class MissingMagicGetException extends RuntimeException
{
    /**
     * @param class-string $className
     */
    public static function cannotFindMagicGet(string $className): self
    {
        return new self('You cannot use a @MagicField annotation on an object that does not implement the __get() magic method. The class ' . $className . ' must implement a __get() method.');
    }
}
