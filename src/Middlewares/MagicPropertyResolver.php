<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use Webmozart\Assert\Assert;
use function get_class;
use function is_object;
use function method_exists;

/**
 * A class that represents a magic property of an object.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class MagicPropertyResolver implements ResolverInterface
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

    /**
     * @param mixed $args
     *
     * @return mixed
     */
    public function __invoke(...$args)
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
        $class = $this->getObject();
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $class . '::__get(\'' . $this->propertyName . '\')';
    }
}
