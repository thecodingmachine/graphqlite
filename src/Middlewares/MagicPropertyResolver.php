<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function method_exists;

/**
 * Resolves field by getting the value of $propertyName from the source object through magic getter __get.
 *
 * @internal
 */
final class MagicPropertyResolver implements ResolverInterface
{
    public function __construct(
        private readonly string $className,
        private readonly string $propertyName,
    ) {
    }

    public function executionSource(object|null $source): object
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for MagicPropertyResolver.');
        }

        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for MagicPropertyResolver.');
        }

        if (! method_exists($source, '__get')) {
            throw MissingMagicGetException::cannotFindMagicGet($source::class);
        }

        return $source->__get($this->propertyName);
    }

    public function toString(): string
    {
        return $this->className . '::__get(\'' . $this->propertyName . '\')';
    }
}
