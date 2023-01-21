<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function assert;
use function is_callable;

/**
 * A class that represents a callable on an object.
 * The object can be modified after class invocation.
 *
 * @internal
 */
final class SourceResolver implements SourceResolverInterface
{
    private object|null $object = null;

    public function __construct(private readonly string $methodName)
    {
    }

    public function setObject(object $object): void
    {
        $this->object = $object;
    }

    public function getObject(): object
    {
        assert($this->object !== null);
        return $this->object;
    }

    public function __invoke(mixed ...$args): mixed
    {
        if ($this->object === null) {
            throw new GraphQLRuntimeException('You must call "setObject" on SourceResolver before invoking the object.');
        }
        $callable = [$this->object, $this->methodName];
        assert(is_callable($callable));

        return $callable(...$args);
    }

    public function toString(): string
    {
        $class = $this->getObject()::class;
        return $class . '::' . $this->methodName . '()';
    }
}
