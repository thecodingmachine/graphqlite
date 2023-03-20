<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function assert;
use function is_callable;

/**
 * Resolves field by calling the specified $methodName on the source object.
 *
 * @internal
 */
final class SourceMethodResolver implements ResolverInterface
{
    public function __construct(
        private readonly string $className,
        private readonly string $methodName,
    )
    {
    }

    public function executionSource(?object $source): object
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceMethodResolver.');
        }

        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceMethodResolver.');
        }

        $callable = [$source, $this->methodName];
        assert(is_callable($callable));

        return $callable(...$args);
    }

    public function toString(): string
    {
        return $this->className . '::' . $this->methodName . '()';
    }
}
