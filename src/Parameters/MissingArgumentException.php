<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use function get_class;
use function is_array;
use function is_string;
use function sprintf;

class MissingArgumentException extends \BadMethodCallException
{
    public static function create(string $argumentName): self
    {
        return new self("Expected argument '$argumentName' was not provided");
    }

    public static function wrapWithFactoryContext(self $previous, string $inputType, callable $callable): self
    {
        $message = sprintf('%s in GraphQL input type \'%s\' used in factory \'%s\'',
            $previous->getMessage(),
            $inputType,
            self::toMethod($callable)
            );

        return new self($message, 0, $previous);
    }

    public static function wrapWithDecoratorContext(self $previous, string $inputType, callable $callable): self
    {
        $message = sprintf('%s in GraphQL input type \'%s\' used in decorator \'%s\'',
            $previous->getMessage(),
            $inputType,
            self::toMethod($callable)
        );

        return new self($message, 0, $previous);
    }

    public static function wrapWithFieldContext(self $previous, string $name, callable $callable): self
    {
        $message = sprintf('%s in GraphQL query/mutation/field \'%s\' used in method \'%s\'',
            $previous->getMessage(),
            $name,
            self::toMethod($callable)
        );

        return new self($message, 0, $previous);
    }

    private static function toMethod(callable $callable): string
    {
        if (!is_array($callable)) {
            return '';
        }
        if (is_string($callable[0])) {
            $factoryName = $callable[0];
        } else {
            $factoryName = get_class($callable[0]);
        }
        return $factoryName.'::'.$callable[1].'()';
    }
}
