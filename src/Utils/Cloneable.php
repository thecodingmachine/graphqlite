<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use ReflectionClass;

use function array_key_exists;

/**
 * This works around the limitations of PHP 8.1 of being unable to clone readonly properties. This
 * has been fixed in the readonly amendments RFC, accepted for PHP 8.3, but for now this is the workaround.
 */
trait Cloneable
{
    /** @param array<string, mixed> ...$values */
    public function with(...$values): static
    {
        $refClass = new ReflectionClass(static::class);
        $clone = $refClass->newInstanceWithoutConstructor();

        foreach ($refClass->getProperties() as $refProperty) {
            if ($refProperty->isStatic()) {
                continue;
            }

            $objectField = $refProperty->getName();

            if (! array_key_exists($objectField, $values) && ! $refProperty->isInitialized($this)) {
                continue;
            }

            $objectValue = array_key_exists($objectField, $values) ? $values[$objectField] : $refProperty->getValue($this);

            $refProperty->setValue($clone, $objectValue);
        }

        return $clone;
    }
}
