<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use function gettype;

/**
 * An exception thrown when a resolver returns a value that is not compatible with the GraphQL type.
 */
class TypeMismatchRuntimeException extends GraphQLRuntimeException
{
    public static function unexpectedNullValue(): self
    {
        return new self('Unexpected null value for non nullable field.');
    }

    /**
     * @param mixed $result
     */
    public static function expectedIterable($result): self
    {
        return new self('Expected resolved value to be iterable but got "' . gettype($result) . '"');
    }

    /**
     * @param mixed $result
     */
    public static function expectedObject($result): self
    {
        return new self('Expected resolved value to be an object but got "' . gettype($result) . '"');
    }

    public function addInfo(string $fieldName, string $location): void
    {
        $this->message = 'In ' . $location . ' (declaring field "' . $fieldName . '"): ' . $this->message;
    }
}
