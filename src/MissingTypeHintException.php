<?php
namespace TheCodingMachine\GraphQLite;

use ReflectionMethod;
use \ReflectionParameter;

class MissingTypeHintException extends GraphQLException
{
    public static function missingTypeHint(ReflectionParameter $parameter): self
    {
        return new self(sprintf('Parameter "%s" of method "%s::%s" is missing a type-hint', $parameter->getName(), $parameter->getDeclaringClass()->getName(), $parameter->getDeclaringFunction()->getName()));
    }

    public static function missingReturnType(ReflectionMethod $method): self
    {
        return new self(sprintf('Factory "%s::%s" must have a return type.', $method->getDeclaringClass()->getName(), $method->getName()));
    }

    public static function invalidReturnType(ReflectionMethod $method): self
    {
        return new self(sprintf('The return type of factory "%s::%s" must be an object, "%s" passed instead.', $method->getDeclaringClass()->getName(), $method->getName(), $method->getReturnType() ? $method->getReturnType()->getName() : 'mixed'));
    }

    public static function nullableReturnType(ReflectionMethod $method): self
    {
        return new self(sprintf('Factory "%s::%s" must have a non nullable return type.', $method->getDeclaringClass()->getName(), $method->getName()));
    }
}
