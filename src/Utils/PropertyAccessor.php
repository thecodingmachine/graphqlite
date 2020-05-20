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
    static public function findGetter(string $class, string $propertyName): ?string
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
     * @param object $object
     * @param string $propertyName
     * @param mixed  ...$args
     *
     * @return mixed
     */
    static public function getValue(object $object, string $propertyName, ...$args)
    {
        if ($method = self::findGetter(get_class($object), $propertyName)) {
            return $object->$method(...$args);
        }

        return $object->$propertyName;
    }
}
