<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

use function assert;

/**
 * A class that represents a callable on an object to resolve property value.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class SourceInputPropertyResolver implements SourceResolverInterface
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
        $object = $this->object;
        assert($object !== null);

        return $object;
    }

    public function __invoke(mixed ...$args): mixed
    {
        if ($this->object === null) {
            throw new GraphQLRuntimeException('You must call "setObject" on SourceResolver before invoking the object.');
        }
        PropertyAccessor::setValue($this->object, $this->propertyName, ...$args);
        return $args[0];
    }

    public function toString(): string
    {
        $class = $this->getObject()::class;

        return $class . '::' . $this->propertyName;
    }
}
