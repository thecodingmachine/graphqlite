<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use ReflectionProperty;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

/**
 * Resolves field by setting the value of $propertyName on the source object.
 *
 * @internal
 */
final class SourceInputPropertyResolver implements ResolverInterface
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

    public function executionSource(object|null $source): object|null
    {
        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceInputPropertyResolver.');
        }

        PropertyAccessor::setValue($source, $this->propertyReflection->getName(), ...$args);

        return $args[0];
    }

    public function toString(): string
    {
        return $this->propertyReflection->getDeclaringClass()->getName() . '::' . $this->propertyReflection->getName();
    }
}
