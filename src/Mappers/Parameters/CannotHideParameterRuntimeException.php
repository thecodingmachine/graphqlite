<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function assert;

class CannotHideParameterRuntimeException extends GraphQLRuntimeException
{
    public static function needDefaultValue(ReflectionParameter $parameter): self
    {
        $method = $parameter->getDeclaringFunction();
        assert($method instanceof ReflectionMethod);
        return new self('For parameter $' . $parameter->getName() . ' of method ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '(), cannot use the @HideParameter annotation. The parameter needs to provide a default value.');
    }
}
