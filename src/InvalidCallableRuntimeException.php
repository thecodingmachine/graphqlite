<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

class InvalidCallableRuntimeException extends GraphQLRuntimeException
{
    public static function methodNotFound(string $className, string $methodName, Throwable $previous = null): self
    {
        return new self("Method {$className}::{$methodName} wasn't found or isn't accessible.", 0, $previous);
    }
}
