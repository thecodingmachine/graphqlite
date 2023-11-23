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
    /**
     * @param class-string $className
     */
    public function __construct(
        private readonly string $className,
        private readonly string $propertyName,
    ) {
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }

    public function executionSource(object|null $source): object|null
    {
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
