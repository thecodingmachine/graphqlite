<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;
use Webmozart\Assert\Assert;

use function is_object;

/**
 * A class that represents a callable on an object to resolve property value.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class SourceInputPropertyResolver implements SourceResolverInterface
{
    /** @var string */
    private $propertyName;

    /** @var object|null */
    private $object;

    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
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
        PropertyAccessor::setValue($this->object, $this->propertyName, ...$args);
        return $args[0];
    }

    public function toString(): string
    {
        $class = $this->getObject();
        if (is_object($class)) {
            $class = $class::class;
        }

        return $class . '::' . $this->propertyName;
    }
}
