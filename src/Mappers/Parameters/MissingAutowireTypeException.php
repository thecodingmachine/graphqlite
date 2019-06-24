<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use Exception;
use ReflectionParameter;
use Webmozart\Assert\Assert;

class MissingAutowireTypeException extends Exception
{
    public static function create(ReflectionParameter $refParameter): self
    {
        $declaringClass = $refParameter->getDeclaringClass();
        Assert::notNull($declaringClass, 'Parameter passed must be a parameter of a method, not a parameter of a function.');

        return new self('For parameter $' . $refParameter->getName() . ' in ' . $declaringClass->getName() . '::' . $refParameter->getDeclaringFunction()->getName() . ', annotated with annotation @Autowire, you must either provide a type-hint or specify the container identifier with @Autowire(identifier="my_service")');
    }
}
