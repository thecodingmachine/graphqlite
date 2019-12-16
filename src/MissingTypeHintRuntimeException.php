<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionMethod;
use ReflectionNamedType;
use function assert;
use function sprintf;

class MissingTypeHintRuntimeException extends GraphQLRuntimeException
{
    public static function missingReturnType(ReflectionMethod $method): self
    {
        return new self(sprintf('Factory "%s::%s" must have a return type.', $method->getDeclaringClass()->getName(), $method->getName()));
    }

    public static function invalidReturnType(ReflectionMethod $method): self
    {
        $returnType = $method->getReturnType();
        assert($returnType === null || $returnType instanceof ReflectionNamedType);
        return new self(sprintf('The return type of factory "%s::%s" must be an object, "%s" passed instead.', $method->getDeclaringClass()->getName(), $method->getName(), $returnType ? $returnType->getName() : 'mixed'));
    }

    public static function nullableReturnType(ReflectionMethod $method): self
    {
        return new self(sprintf('Factory "%s::%s" must have a non nullable return type.', $method->getDeclaringClass()->getName(), $method->getName()));
    }
}
