<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use ReflectionMethod;
use ReflectionProperty;
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
            if (self::publicMethodExists($class, $methodName)) {
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
        if (self::publicMethodExists($class, $methodName)) {
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
        $class = get_class($object);

        $method = self::findGetter($class, $propertyName);
        if ($method) {
            return $object->$method(...$args);
        }

        if (self::publicPropertyExists($class, $propertyName)) {
            return $object->$propertyName;
        }

        throw AccessPropertyException::createForUnreadableProperty($class, $propertyName);
    }

    /**
     * @param mixed  $value
     */
    public static function setValue(object $instance, string $propertyName, $value): void
    {
        $class = get_class($instance);

        $setter = self::findSetter($class, $propertyName);
        if ($setter) {
            $instance->$setter($value);
            return;
        }

        if (self::publicPropertyExists($class, $propertyName)) {
            $instance->$propertyName = $value;
        }

        throw AccessPropertyException::createForUnwritableProperty($class, $propertyName);
    }

    private static function publicPropertyExists(string $class, string $propertyName): bool
    {
        if (!property_exists($class, $propertyName)) {
            return false;
        }

        $reflection = new ReflectionProperty($class, $propertyName);

        return $reflection->isPublic();
    }

    private static function publicMethodExists(string $class, string $methodName): bool
    {
        if (!method_exists($class, $methodName)) {
            return false;
        }

        $reflection = new ReflectionMethod($class, $methodName);

        return $reflection->isPublic();
    }
}
