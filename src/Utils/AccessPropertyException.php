<?php

namespace TheCodingMachine\GraphQLite\Utils;

use LogicException;

class AccessPropertyException extends LogicException
{
    public static function createForUnreadableProperty(string $class, string $propertyName): self
    {
        $name = ucfirst($propertyName);

        return new self("Could not get value from property '$class::$propertyName'. Either make the property public or add a public getter for it like 'get$name' or 'is$name' with no required parameters");
    }

    public static function createForUnwritableProperty(string $class, string $propertyName): self
    {
        $name = ucfirst($propertyName);

        return new self("Could not set value for property '$class::$propertyName'. Either make the property public or add a public setter for it like this: 'set$name'");
    }
}
