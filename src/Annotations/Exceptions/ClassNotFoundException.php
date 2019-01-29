<?php


namespace TheCodingMachine\GraphQLite\Annotations\Exceptions;


use InvalidArgumentException;

class ClassNotFoundException extends InvalidArgumentException
{
    public static function couldNotFindClass(string $className): self
    {
        return new self("Could not autoload class '$className'");
    }

    public static function wrapException(self $e, string $className): self
    {
        return new self($e->getMessage()." defined in @Type annotation of class '$className'");
    }

    public static function wrapExceptionForExtendTag(self $e, string $className): self
    {
        return new self($e->getMessage()." defined in @ExtendType annotation of class '$className'");
    }
}
