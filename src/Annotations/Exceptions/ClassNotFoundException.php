<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations\Exceptions;

use InvalidArgumentException;
use function sprintf;

class ClassNotFoundException extends InvalidArgumentException
{
    public static function couldNotFindClass(string $className): self
    {
        return new self(sprintf("Could not autoload class '%s'", $className));
    }

    public static function wrapException(self $e, string $className): self
    {
        return new self($e->getMessage() . " defined in @Type annotation of class '" . $className . "'");
    }

    public static function wrapExceptionForExtendTag(self $e, string $className): self
    {
        return new self($e->getMessage() . " defined in @ExtendType annotation of class '" . $className . "'");
    }
}
