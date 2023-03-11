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
 * The ExtendType annotation must be put in a GraphQL type class docblock and is used to add additional fields to the underlying PHP class.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 *   @Attribute("name", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ExtendType
{
    /** @var class-string<object>|null */
    private $class;

    /** @var string|null */
    private $name;

    /** @param mixed[] $attributes */
    public function __construct(array $attributes = [], string|null $class = null, string|null $name = null)
    {
        $className = isset($attributes['class']) ? ltrim($attributes['class'], '\\') : null;
        $className = $className ?? $class;
        if ($className !== null && ! class_exists($className) && ! interface_exists($className)) {
            throw ClassNotFoundException::couldNotFindClass($className);
        }
        $this->name = $name ?? $attributes['name'] ?? null;
        $this->class = $className;
        if (! $this->class && ! $this->name) {
            throw new BadMethodCallException('In annotation @ExtendType, missing one of the compulsory parameter "class" or "name".');
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
