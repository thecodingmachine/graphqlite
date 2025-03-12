<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

/**
 * Factories are methods used to declare GraphQL input types.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Factory
{
    private string|null $name;
    private bool $default;

    /** @param mixed[] $attributes */
    public function __construct(array $attributes = [], string|null $name = null, bool|null $default = null)
    {
        $this->name = $name ?? $attributes['name'] ?? null;
        // This IS the default if no name is set and no "default" attribute is passed.
        $this->default = $default ?? $attributes['default'] ?? ! isset($attributes['name']);

        if ($this->name === null && $this->default === false) {
            throw new GraphQLRuntimeException('A #[Factory] that has "default=false" attribute must be given a name (i.e. add a name="FooBarInput" attribute).');
        }
    }

    /**
     * Returns the name of the GraphQL input type.
     * If not specified, the name of the method should be used instead.
     */
    public function getName(): string|null
    {
        return $this->name;
    }

    /**
     * Returns true if this factory should map the return type of the factory by default.
     */
    public function isDefault(): bool
    {
        return $this->default;
    }
}
