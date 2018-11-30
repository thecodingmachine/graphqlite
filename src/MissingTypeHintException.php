<?php
namespace TheCodingMachine\GraphQL\Controllers;

use \ReflectionParameter;

class MissingTypeHintException extends GraphQLException
{
    public static function missingTypeHint(ReflectionParameter $parameter): self
    {
        return new self(sprintf('Parameter "%s" of method "%s::%s" is missing a type-hint', $parameter->getName(), $parameter->getDeclaringClass()->getName(), $parameter->getDeclaringFunction()->getName()));
    }
}