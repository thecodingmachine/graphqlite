<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionMethod;
use ReflectionProperty;

class InvalidDocBlockRuntimeException extends GraphQLRuntimeException
{
    public static function tooManyReturnTags(ReflectionMethod $refMethod): self
    {
        throw new self('Method ' . $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . ' has several @return annotations.');
    }

    /**
     * Creates an exception for property to have multiple var tags.
     *
     * @param ReflectionProperty $refProperty
     */
    public static function tooManyVarTags(ReflectionProperty $refProperty)
    {
        throw new self('Property ' . $refProperty->getDeclaringClass()->getName() . '::' . $refProperty->getName() . ' has several @var annotations.');
    }
}
