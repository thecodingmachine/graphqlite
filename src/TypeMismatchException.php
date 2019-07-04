<?php


namespace TheCodingMachine\GraphQLite;

/**
 * An exception thrown when a resolver returns a value that is not compatible with the GraphQL type.
 */
class TypeMismatchException extends GraphQLException
{
    public static function unexpectedNullValue(): self
    {
        return new self('Unexpected null value for non nullable field.');
    }

    public static function expectedIterable($result): self
    {
        return new self('Expected resolved value to be iterable but got "'.gettype($result).'"');
    }

    public static function expectedObject($result): self
    {
        return new self('Expected resolved value to be an object but got "'.gettype($result).'"');
    }

    public function addInfo(string $fieldName, string $className, string $methodName): void
    {
        $this->message = 'In '.$className.'::'.$methodName.'() (declaring field "'.$fieldName.'"): ' . $this->message;
    }
}