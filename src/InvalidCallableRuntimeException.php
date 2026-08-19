<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Throwable;

class InvalidCallableRuntimeException extends GraphQLRuntimeException
{
    public static function methodNotFound(string $className, string $methodName, Throwable|null $previous = null): self
    {
        return new self('Method ' . $className . '::' . $methodName . " wasn't found or isn't accessible.", 0, $previous);
    }

    public static function methodNotPublic(string $className, string $methodName): self
    {
        return new self(
            'Method ' . $className . '::' . $methodName . ' must be public to be used as a callable. On PHP 8.5 and later, '
            . 'first-class callable syntax written inside the declaring class keeps the class scope and can therefore name a '
            . 'private method: ' . $className . '::' . $methodName . '(...).',
        );
    }

    public static function notInvokable(string $className, Throwable|null $previous = null): self
    {
        return new self(
            'Object of class ' . $className . ' cannot be used as a callable because it has no __invoke() method.',
            0,
            $previous,
        );
    }

    public static function noClassContext(string $methodName): self
    {
        return new self(
            'The bare method name "' . $methodName . '" cannot be resolved because no class context was provided. '
            . 'Name the class explicitly instead, for instance [MyRules::class, "' . $methodName . '"].',
        );
    }

    public static function notAMethod(): self
    {
        return new self(
            'The callable must name a real method, because its parameters are mapped to GraphQL arguments and their '
            . 'descriptions are read from the method docblock. An inline closure has no such method. Use an array '
            . 'callable, an invokable object, or first-class callable syntax on PHP 8.5 and later.',
        );
    }
}
