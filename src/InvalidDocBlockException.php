<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionMethod;

class InvalidDocBlockException extends GraphQLException
{
    public static function tooManyReturnTags(ReflectionMethod $refMethod) : self
    {
        throw new self('Method ' . $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . ' has several @return annotations.');
    }
}
