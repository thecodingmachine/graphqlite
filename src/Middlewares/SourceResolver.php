<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use Webmozart\Assert\Assert;

/**
 * A class that represents a callable on an object.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class SourceResolver implements SourceResolverInterface
{
    private ?object $object = null;

    public function __construct(private string $methodName)
    {
    }

    public function setObject(object $object): void
    {
        $this->object = $object;
    }

    public function getObject(): object
    {
        Assert::notNull($this->object);

        return $this->object;
    }

    public function __invoke(mixed ...$args): mixed
    {
        if ($this->object === null) {
            throw new GraphQLRuntimeException('You must call "setObject" on SourceResolver before invoking the object.');
        }
        $callable = [$this->object, $this->methodName];
        Assert::isCallable($callable);

        return $callable(...$args);
    }

    public function toString(): string
    {
        $class = $this->getObject()::class;
        return $class . '::' . $this->methodName . '()';
    }
}
