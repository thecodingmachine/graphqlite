<?php

namespace TheCodingMachine\GraphQLite\Utils;

/**
 * Util class that helps accessing class properties.
 */
class PropertyAccessor
{

    /**
     * Finds a getter for a property.
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return string|null
     */
    public static function findGetter(string $class, string $propertyName): ?string
    {
        $name = ucfirst($propertyName);

        foreach (['get', 'is'] as $prefix) {
            $methodName = "$prefix$name";
            if (method_exists($class, $methodName)) {
                return $methodName;
            }
        }

        return null;
    }

    /**
     * Finds a setter for a property.
     *
     * @param string $class
     * @param string $propertyName
     *
     * @return string|null
     */
    public static function findSetter(string $class, string $propertyName): ?string
    {
        $name = ucfirst($propertyName);

        $methodName = "set$name";
        if (method_exists($class, $methodName)) {
            return $methodName;
        }

        return null;
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed  ...$args
     *
     * @return mixed
     */
    public static function getValue(object $object, string $propertyName, ...$args)
    {
        if ($method = self::findGetter(get_class($object), $propertyName)) {
            return $object->$method(...$args);
        }

        return $object->$propertyName;
    }

    /**
     * @param object $instance
     * @param string $propertyName
     * @param mixed  $value
     */
    public static function setValue(object $instance, string $propertyName, $value): void
    {
        if ($setter = self::findSetter(get_class($instance), $propertyName)) {
            $instance->$setter($value);
        } else {
            $instance->$propertyName = $value;
        }
    }
}
