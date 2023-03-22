<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

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
        private readonly string $className,
        private readonly string $propertyName,
    )
    {
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

        return PropertyAccessor::getValue($source, $this->propertyName, ...$args);
    }

    public function toString(): string
    {
        return $this->className . '::' . $this->propertyName;
    }
}
