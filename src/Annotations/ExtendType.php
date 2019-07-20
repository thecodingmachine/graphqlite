<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function interface_exists;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use function class_exists;
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
class ExtendType
{
    /** @var string|null */
    private $class;
    /** @var string|null */
    private $name;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (! isset($attributes['class']) && ! isset($attributes['name'])) {
            throw new BadMethodCallException('In annotation @ExtendType, missing one of the compulsory parameter "class" or "name".');
        }
        $this->class = $attributes['class'] ?? null;
        $this->name = $attributes['name'] ?? null;
        if ($this->class !== null && ! class_exists($this->class) && ! interface_exists($this->class)) {
            throw ClassNotFoundException::couldNotFindClass($this->class);
        }
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     */
    public function getClass(): ?string
    {
        if ($this->class === null) {
            return null;
        }

        return ltrim($this->class, '\\');
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
