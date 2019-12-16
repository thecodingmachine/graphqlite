<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class InvalidPrefetchMethodRuntimeException extends GraphQLRuntimeException
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public static function methodNotFound(ReflectionMethod $annotationMethod, ReflectionClass $reflectionClass, string $methodName, ReflectionException $previous): self
    {
        throw new self('The @Field annotation in ' . $annotationMethod->getDeclaringClass()->getName() . '::' . $annotationMethod->getName() . ' specifies a "prefetch method" that could not be found. Unable to find method ' . $reflectionClass->getName() . '::' . $methodName . '.', 0, $previous);
    }

    public static function prefetchDataIgnored(ReflectionMethod $annotationMethod, bool $isSecond): self
    {
        throw new self('The @Field annotation in ' . $annotationMethod->getDeclaringClass()->getName() . '::' . $annotationMethod->getName() . ' specifies a "prefetch method" but the data from the prefetch method is not gathered. The "' . $annotationMethod->getName() . '" method should accept a ' . ($isSecond?'second':'first') . ' parameter that will contain data returned by the prefetch method.');
    }
}
