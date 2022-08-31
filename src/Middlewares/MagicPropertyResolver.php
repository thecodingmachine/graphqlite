<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function assert;
use function get_class;
use function method_exists;

/**
 * A class that represents a magic property of an object.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class MagicPropertyResolver implements SourceResolverInterface
{
    private object|null $object = null;

    public function __construct(private string $propertyName)
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
            throw new GraphQLRuntimeException('You must call "setObject" on MagicPropertyResolver before invoking the object.');
        }
        if (! method_exists($this->object, '__get')) {
            throw MissingMagicGetException::cannotFindMagicGet(get_class($this->object));
        }

        return $this->object->__get($this->propertyName);
    }

    public function toString(): string
    {
        $class = $this->getObject()::class;
        return $class . '::__get(\'' . $this->propertyName . '\')';
    }
}
