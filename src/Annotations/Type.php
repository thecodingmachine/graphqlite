<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use RuntimeException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function class_exists;
use function interface_exists;
use function ltrim;

/**
 * The Type attribute must be put in a GraphQL type class attribute and is used to map to the underlying PHP class
 * this is exposed via this type.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Type implements TypeInterface
{
    /** @var class-string<object>|null */
    private string|null $class = null;

    private string|null $name = null;

    private bool $default = true;

    /**
     * Is the class having the attribute a GraphQL type itself?
     */
    private bool $selfType = false;

    private bool $useEnumValues = false;

    /**
     * @param mixed[] $attributes
     * @param class-string<object>|null $class
     */
    public function __construct(
        array $attributes = [],
        string|null $class = null,
        string|null $name = null,
        bool|null $default = null,
        bool|null $external = null,
        bool|null $useEnumValues = null,
    ) {
        $external = $external ?? $attributes['external'] ?? null;
        $class = $class ?? $attributes['class'] ?? null;
        if ($class !== null) {
            $this->setClass($class);
        } else {
            $this->selfType = true;
        }

        $this->name = $name ?? $attributes['name'] ?? null;

        // If no value is passed for default, "default" = true
        $this->default = $default ?? $attributes['default'] ?? true;
        $this->useEnumValues = $useEnumValues ?? $attributes['useEnumValues'] ?? false;

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
            throw new RuntimeException('Empty class for #[Type] attribute. You MUST create the Type attribute object using the GraphQLite AnnotationReader');
        }

        return $this->class;
    }

    public function setClass(string $className): void
    {
        $className = ltrim($className, '\\');
        $isInterface = interface_exists($className);
        if (! class_exists($className) && ! $isInterface) {
            throw ClassNotFoundException::couldNotFindClass($className);
        }
        $this->class = $className;

        if (! $isInterface) {
            return;
        }

        if ($this->default === false) {
            throw new GraphQLRuntimeException('Problem in attribute #[Type] for interface "' . $className . '": you cannot use the default="false" attribute on interfaces');
        }
    }

    public function isSelfType(): bool
    {
        return $this->selfType;
    }

    /**
     * Returns the GraphQL output name for this type.
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
     * Returns true if this enum type
     */
    public function useEnumValues(): bool
    {
        return $this->useEnumValues;
    }
}
