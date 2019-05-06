<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use GraphQL\Error\Error;
use GraphQL\Error\SyntaxError;
use GraphQL\Type\Definition\ObjectType;
use ReflectionClass;
use ReflectionMethod;
use function sprintf;
use TheCodingMachine\GraphQLite\Annotations\SourceField;

class MissingArgumentException extends \BadMethodCallException
{
    public static function create(string $argumentName): self
    {
        return new self("Expected argument '$argumentName' was not provided");
    }

    public static function wrapWithFactoryContext(self $previous, string $inputType, string $factoryClass, string $factoryMethod): self
    {
        $message = sprintf('%s in GraphQL input type \'%s\' used in factory \'%s::%s()\'',
            $previous->getMessage(),
            $inputType,
            $factoryClass,
            $factoryMethod
            );

        return new self($message, 0, $previous);
    }

    public static function wrapWithFieldContext(self $previous, string $name, string $factoryClass, string $factoryMethod): self
    {
        $message = sprintf('%s in GraphQL query/mutation/field \'%s\' used in method \'%s::%s()\'',
            $previous->getMessage(),
            $name,
            $factoryClass,
            $factoryMethod
        );

        return new self($message, 0, $previous);
    }
}
