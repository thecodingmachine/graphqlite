<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use LogicException;

use function sprintf;
use function ucfirst;

class AccessPropertyException extends LogicException
{
    public static function createForUnreadableProperty(string $class, string $propertyName): self
    {
        $name = ucfirst($propertyName);

        return new self(sprintf("Could not get value from property '%s::%s'. Either make the property public or add a public getter for it like 'get%s' or 'is%s' with no required parameters", $class, $propertyName, $name, $name));
    }

    public static function createForUnwritableProperty(string $class, string $propertyName): self
    {
        $name = ucfirst($propertyName);

        return new self(sprintf("Could not set value for property '%s::%s'. Either make the property public or add a public setter for it like this: 'set%s'", $class, $propertyName, $name));
    }
}
