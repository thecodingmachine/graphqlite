<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use Doctrine\Common\Annotations\Annotation\Attribute;
use RuntimeException;

/**
 * The Input annotation must be put in a GraphQL input type class docblock and is used to map to the underlying PHP class
 * this is exposed via this input type.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 *   @Attribute("name", type = "string"),
 *   @Attribute("default", type = "bool"),
 *   @Attribute("decsription", type = "string"),
 *   @Attribute("update", type = "bool"),
 * })
 */
class Input
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool
     */
    private $default;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool
     */
    private $update;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->class = $attributes['class'] ?? null;
        $this->name = $attributes['name'] ?? null;
        $this->default = $attributes['default'] ?? !isset($attributes['name']);
        $this->description = $attributes['description'] ?? null;
        $this->update = $attributes['update'] ?? false;
    }

    /**
     * Returns the fully qualified class name of the targeted class.
     *
     * @return string
     */
    public function getClass(): string
    {
        if ($this->class === null) {
            throw new RuntimeException('Empty class for @Input annotation. You MUST create the Input annotation object using the GraphQLite AnnotationReader');
        }

        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * Returns the GraphQL input name for this type.
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

    /**
     * Returns description about this input type.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns true if this type should behave as update resource.
     * Such input type has all fields optional and without default value in the documentation.
     *
     * @return bool
     */
    public function isUpdate(): bool
    {
        return $this->update;
    }
}
