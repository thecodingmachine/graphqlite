<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

class InvalidPrefetchMethodRuntimeException extends GraphQLRuntimeException
{
    /** @deprecated Remove with the removal of old #[Field(prefetchMethod)] */
    public static function methodNotFound(ReflectionMethod|ReflectionProperty $reflector, ReflectionClass $reflectionClass, string $methodName, ReflectionException $previous): self
    {
        throw new self('The @Field annotation in ' . $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName() . ' specifies a "prefetch method" that could not be found. Unable to find method ' . $reflectionClass->getName() . '::' . $methodName . '.', 0, $previous);
    }

    public static function fromInvalidCallable(
        ReflectionMethod $reflector,
        string $parameterName,
        InvalidCallableRuntimeException $e,
    ): self
    {
        return new self(
            '#[Prefetch] attribute on parameter $' . $parameterName . ' in ' . $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName() .
            ' specifies a callable that is invalid: ' . $e->getMessage(),
            previous: $e,
        );
    }
}
