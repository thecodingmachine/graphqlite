<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

use function assert;

/**
 * Resolves field by setting the value of $propertyName on the source object.
 *
 * @internal
 */
final class SourceInputPropertyResolver implements ResolverInterface
{
    public function __construct(
        private readonly string $className,
        private readonly string $propertyName,
    )
    {
    }

    public function executionSource(?object $source): object
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceInputPropertyResolver.');
        }

        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceInputPropertyResolver.');
        }

        PropertyAccessor::setValue($source, $this->propertyName, ...$args);

        return $args[0];
    }

    public function toString(): string
    {
        return $this->className . '::' . $this->propertyName;
    }
}
