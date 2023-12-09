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
        private readonly \ReflectionMethod $methodReflection,
    )
    {
    }

    public function methodReflection(): \ReflectionMethod
    {
        return $this->methodReflection;
    }

    public function executionSource(object|null $source): object|null
    {
        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        if ($source === null) {
            throw new GraphQLRuntimeException('You must provide a source for SourceMethodResolver.');
        }

        $callable = [$source, $this->methodReflection->getName()];
        assert(is_callable($callable));

        return $callable(...$args);
    }

    public function toString(): string
    {
        return $this->methodReflection->getDeclaringClass()->getName() . '::' . $this->methodReflection->getName() . '()';
    }
}
