<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use BadMethodCallException;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use function get_class;
use function is_array;
use function is_string;
use function sprintf;

class MissingArgumentException extends BadMethodCallException implements GraphQLExceptionInterface
{
    public static function create(string $argumentName): self
    {
        return new self("Expected argument '" . $argumentName . "' was not provided");
    }

    public static function wrapWithFactoryContext(self $previous, string $inputType, callable $callable): self
    {
        $message = sprintf(
            '%s in GraphQL input type \'%s\' used in factory \'%s\'',
            $previous->getMessage(),
            $inputType,
            self::toLocation($callable)
        );

        return new self($message, 0, $previous);
    }

    public static function wrapWithDecoratorContext(self $previous, string $inputType, callable $callable): self
    {
        $message = sprintf(
            '%s in GraphQL input type \'%s\' used in decorator \'%s\'',
            $previous->getMessage(),
            $inputType,
            self::toLocation($callable)
        );

        return new self($message, 0, $previous);
    }

    public static function wrapWithFieldContext(self $previous, string $name, callable $callable): self
    {
        $message = sprintf(
            '%s in GraphQL query/mutation/field \'%s\' used in method \'%s\'',
            $previous->getMessage(),
            $name,
            self::toLocation($callable)
        );

        return new self($message, 0, $previous);
    }

    private static function toLocation(callable $callable): string
    {
        if ($callable instanceof ResolverInterface) {
            return $callable->toString();
        }
        if (! is_array($callable)) {
            return '';
        }
        if (is_string($callable[0])) {
            $factoryName = $callable[0];
        } else {
            $factoryName = get_class($callable[0]);
        }

        return $factoryName . '::' . $callable[1] . '()';
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     */
    public function getCategory(): string
    {
        return 'graphql';
    }

    /**
     * Returns the "extensions" object attached to the GraphQL error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return [];
    }
}
