<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use ReflectionMethod;

class CannotMapTypeException extends \Exception
{
    public static function createForType(string $className): self
    {
        return new self('cannot map class "'.$className.'" to a known GraphQL type. Check your TypeMapper configuration.');
    }

    public static function createForInputType(string $className): self
    {
        return new self('cannot map class "'.$className.'" to a known GraphQL input type. Check your TypeMapper configuration.');
    }

    public static function wrapWithParamInfo(self $previous, \ReflectionParameter $parameter): self
    {
        $message = sprintf('For parameter $%s, in %s::%s, %s',
            $parameter->getName(),
            $parameter->getDeclaringClass()->getName(),
            $parameter->getDeclaringFunction()->getName(),
            $previous->getMessage());

        return new self($message, 0, $previous);
    }

    public static function wrapWithReturnInfo(self $previous, ReflectionMethod $method): self
    {
        $message = sprintf('For return type of %s::%s, %s',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $previous->getMessage());

        return new self($message, 0, $previous);
    }
}
