<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use Webmozart\Assert\Assert;
use function get_class;
use function is_object;

/**
 * A class that represents a callable on an object to resolve property value.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class SourcePropertyResolver implements SourceResolverInterface
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var object|null
     */
    private $object;

    /**
     * @param string $propertyName
     */
    public function __construct(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function setObject(object $object): void
    {
        $this->object = $object;
    }

    /**
     * {@inheritdoc}
     */
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
            throw new GraphQLRuntimeException('You must call "setObject" on SourceResolver before invoking the object.');
        }

        return PropertyAccessor::getValue($this->object, $this->propertyName, ...$args);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        $class = $this->getObject();
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $class . '::' . $this->propertyName;
    }
}
