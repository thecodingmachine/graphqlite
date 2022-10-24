<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use RuntimeException;

/**
 * The Input annotation must be put in a GraphQL input type class docblock and is used to map to the underlying PHP class
 * this is exposed via this input type.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("default", type = "bool"),
 *   @Attribute("description", type = "string"),
 *   @Attribute("update", type = "bool"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Input implements TypeInterface
{
    /** @var class-string<object>|null */
    private string|null $class = null;

    private string|null $name = null;

    private bool $default;

    private string|null $description = null;

    private bool $update;

    /** @param mixed[] $attributes */
    public function __construct(
        array $attributes = [],
        string|null $name = null,
        bool|null $default = null,
        string|null $description = null,
        bool|null $update = null,
    ) {
        $this->name = $name ?? $attributes['name'] ?? null;
        $this->default = $default ?? $attributes['default'] ?? $this->name === null;
        $this->description = $description ?? $attributes['description'] ?? null;
        $this->update = $update ?? $attributes['update'] ?? false;
    }

    /**
     * Returns the fully qualified class name of the targeted class.
     *
     * @return class-string<object>
     */
    public function getClass(): string
    {
        if ($this->class === null) {
            throw new RuntimeException('Empty class for @Input annotation. You MUST create the Input annotation object using the GraphQLite AnnotationReader');
        }

        return $this->class;
    }

    /** @param class-string<object> $class */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * Returns the GraphQL input name for this type.
     */
    public function getName(): string|null
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
     */
    public function getDescription(): string|null
    {
        return $this->description;
    }

    /**
     * Returns true if this type should behave as update resource.
     * Such input type has all fields optional and without default value in the documentation.
     */
    public function isUpdate(): bool
    {
        return $this->update;
    }

    /**
     * By default there isn't support for defining the type outside
     * This is used by the @Type annotation with the "external" attribute.
     */
    public function isSelfType(): bool
    {
        return true;
    }
}
