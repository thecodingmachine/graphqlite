<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function method_exists;
use function property_exists;
use function ucfirst;

/**
 * Util class that helps accessing class properties.
 */
class PropertyAccessor
{
    /**
     * Finds a getter for a property.
     */
    public static function findGetter(string $class, string $propertyName): string|null
    {
        foreach (['get', 'is'] as $prefix) {
            $methodName = self::propertyToMethodName($prefix, $propertyName);

            if (self::isPublicMethod($class, $methodName)) {
                return $methodName;
            }
        }

        return null;
    }

    /**
     * Finds a setter for a property.
     */
    public static function findSetter(string $class, string $propertyName): string|null
    {
        $methodName = self::propertyToMethodName('set', $propertyName);

        if (self::isPublicMethod($class, $methodName)) {
            return $methodName;
        }

        return null;
    }

    public static function getValue(object $object, string $propertyName, mixed ...$args): mixed
    {
        $class = $object::class;

        $method = self::findGetter($class, $propertyName);
        if ($method && self::isValidGetter($class, $method)) {
            return $object->$method(...$args);
        }

        if (self::isPublicProperty($class, $propertyName)) {
            return $object->$propertyName;
        }

        if (method_exists($class, '__call')) {
            $method = self::propertyToMethodName('get', $propertyName);

            return $object->$method(...$args);
        }

        throw AccessPropertyException::createForUnreadableProperty($class, $propertyName);
    }

    public static function setValue(object $instance, string $propertyName, mixed $value): void
    {
        $class = $instance::class;

        $setter = self::findSetter($class, $propertyName);
        if ($setter) {
            $instance->$setter($value);
            return;
        }

        if (self::isPublicProperty($class, $propertyName)) {
            $instance->$propertyName = $value;
            return;
        }

        if (method_exists($class, '__call')) {
            $method = self::propertyToMethodName('set', $propertyName);

            $instance->$method($value);
            return;
        }

        throw AccessPropertyException::createForUnwritableProperty($class, $propertyName);
    }

    private static function isPublicProperty(string $class, string $propertyName): bool
    {
        if (! property_exists($class, $propertyName)) {
            return false;
        }

        return (new ReflectionProperty($class, $propertyName))->isPublic();
    }

    private static function isPublicMethod(string $class, string $methodName): bool
    {
        if (! method_exists($class, $methodName)) {
            return false;
        }

        return (new ReflectionMethod($class, $methodName))->isPublic();
    }

    /** @throws ReflectionException */
    private static function isValidGetter(string $class, string $methodName): bool
    {
        $reflection = new ReflectionMethod($class, $methodName);
        foreach ($reflection->getParameters() as $parameter) {
            if (! $parameter->isDefaultValueAvailable()) {
                return false;
            }
        }

        return true;
    }

    private static function propertyToMethodName(string $prefix, string $propertyName): string
    {
        return $prefix . ucfirst($propertyName);
    }
}
