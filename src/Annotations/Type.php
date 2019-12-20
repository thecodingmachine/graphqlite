<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use RuntimeException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use function class_exists;
use function interface_exists;
use function ltrim;

/**
 * The Type annotation must be put in a GraphQL type class docblock and is used to map to the underlying PHP class
 * this is exposed via this type.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 *   @Attribute("name", type = "string"),
 *   @Attribute("default", type = "bool"),
 *   @Attribute("external", type = "bool"),
 * })
 */
class Type
{
    /** @var class-string<object>|null */
    private $class;

    /** @var string|null */
    private $name;

    /** @var bool */
    private $default;

    /**
     * Is the class having the annotation a GraphQL type itself?
     *
     * @var bool
     */
    private $selfType = false;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $external = $attributes['external'] ?? null;
        if (isset($attributes['class'])) {
            $this->setClass($attributes['class']);
        } else {
            $this->selfType = true;
        }

        $this->name = $attributes['name'] ?? null;

        // If no value is passed for default, "default" = true
        $this->default = $attributes['default'] ?? true;

        if ($external === null) {
            return;
        }

        $this->selfType = ! $external;
    }

    /**
     * Returns the fully qualified class name of the targeted class.
     *
     * @return class-string<object>
     */
    public function getClass(): string
    {
        if ($this->class === null) {
            throw new RuntimeException('Empty class for @Type annotation. You MUST create the Type annotation object using the GraphQLite AnnotationReader');
        }

        return $this->class;
    }

    public function setClass(string $class): void
    {
        $class = ltrim($class, '\\');
        $isInterface = interface_exists($class);
        if (! class_exists($class) && ! $isInterface) {
            throw ClassNotFoundException::couldNotFindClass($class);
        }
        $this->class = $class;

        if (! $isInterface) {
            return;
        }

        if ($this->default === false) {
            throw new GraphQLRuntimeException('Problem in annotation @Type for interface "' . $class . '": you cannot use the default="false" attribute on interfaces');
        }
    }

    public function isSelfType(): bool
    {
        return $this->selfType;
    }

    /**
     * Returns the GraphQL output name for this type.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns true if this type should map the targeted class by default.
     */
    public function isDefault(): bool
    {
        return $this->default;
    }
}
