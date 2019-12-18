<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations\Exceptions;

use BadMethodCallException;
use ReflectionMethod;
use function sprintf;

class InvalidParameterException extends BadMethodCallException
{
    public static function parameterNotFound(string $parameter, string $annotationClass, ReflectionMethod $reflectionMethod): self
    {
        return new self(sprintf('Parameter "%s" declared in annotation "%s" of method "%s::%s()" does not exist.', $parameter, $annotationClass, $reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName()));
    }

    public static function parameterNotFoundFromSourceField(string $parameter, string $annotationClass, ReflectionMethod $reflectionMethod): self
    {
        return new self(sprintf('Could not find parameter "%s" declared in annotation "%s". This annotation is itself declared in a SourceField annotation targeting resolver "%s::%s()".', $parameter, $annotationClass, $reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName()));
    }
}
