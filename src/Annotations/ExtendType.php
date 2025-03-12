<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;

use function class_exists;
use function interface_exists;
use function ltrim;

/**
 * The ExtendType attribute must be put in a GraphQL type class docblock and is used to add additional fields to the underlying PHP class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ExtendType
{
    /** @var class-string<object>|null */
    private string|null $class;
    private string|null $name;

    /** @param mixed[] $attributes */
    public function __construct(
        array $attributes = [],
        string|null $class = null,
        string|null $name = null,
    ) {
        $className = isset($attributes['class']) ? ltrim($attributes['class'], '\\') : null;
        $className = $className ?? $class;
        if ($className !== null && ! class_exists($className) && ! interface_exists($className)) {
            throw ClassNotFoundException::couldNotFindClass($className);
        }
        $this->name = $name ?? $attributes['name'] ?? null;
        $this->class = $className;
        if (! $this->class && ! $this->name) {
            throw new BadMethodCallException('In attribute #[ExtendType], missing one of the compulsory parameter "class" or "name".');
        }
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     *
     * @return class-string<object>|null
     */
    public function getClass(): string|null
    {
        return $this->class;
    }

    public function getName(): string|null
    {
        return $this->name;
    }
}
