<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use ReflectionProperty;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

/**
 * Resolves field by getting the value of $propertyName from the source object.
 *
 * @internal
 */
final class SourcePropertyResolver implements ResolverInterface
{
    public function __construct(
        private readonly ReflectionProperty $propertyReflection,
    )
    {
    }

    public function propertyReflection(): ReflectionProperty
    {
        return $this->propertyReflection;
    }

    public function executionSource(object|null $source): object
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourcePropertyResolver.');
        }

        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourcePropertyResolver.');
        }

        return PropertyAccessor::getValue($source, $this->propertyReflection->getName(), ...$args);
    }

    public function toString(): string
    {
        return $this->propertyReflection->getDeclaringClass()->getName() . '::' . $this->propertyReflection->getName();
    }
}
