<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use function get_class;
use function method_exists;
use function ucfirst;

/**
 * Util class that helps accessing class properties.
 */
class PropertyAccessor
{
    /**
     * Finds a getter for a property.
     */
    public static function findGetter(string $class, string $propertyName): ?string
    {
        $name = ucfirst($propertyName);

        foreach (['get', 'is'] as $prefix) {
            $methodName = $prefix . $name;
            if (method_exists($class, $methodName)) {
                return $methodName;
            }
        }

        return null;
    }

    /**
     * Finds a setter for a property.
     */
    public static function findSetter(string $class, string $propertyName): ?string
    {
        $name = ucfirst($propertyName);

        $methodName = 'set' . $name;
        if (method_exists($class, $methodName)) {
            return $methodName;
        }

        return null;
    }

    /**
     * @param mixed  ...$args
     *
     * @return mixed
     */
    public static function getValue(object $object, string $propertyName, ...$args)
    {
        $method = self::findGetter(get_class($object), $propertyName);
        if ($method) {
            return $object->$method(...$args);
        }

        return $object->$propertyName;
    }

    /**
     * @param mixed  $value
     */
    public static function setValue(object $instance, string $propertyName, $value): void
    {
        $setter = self::findSetter(get_class($instance), $propertyName);
        if ($setter) {
            $instance->$setter($value);
        } else {
            $instance->$propertyName = $value;
        }
    }
}
