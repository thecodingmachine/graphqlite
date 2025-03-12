<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * The EnumType annotation is useful to change the name of the generated "enum" type.
 *
 * @deprecated Use @Type on a native PHP 8.1 Enum instead. Support will be removed in future release.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EnumType
{
    private string|null $name;
    private bool $useValues;

    /** @param mixed[] $attributes */
    public function __construct(array $attributes = [], string|null $name = null, bool|null $useValues = null)
    {
        $this->name = $name ?? $attributes['name'] ?? null;
        $this->useValues = $useValues ?? $attributes['useValues'] ?? false;
    }

    /**
     * Returns the GraphQL name for this type.
     */
    public function getName(): string|null
    {
        return $this->name;
    }

    /**
     * Returns true if the enum type should expose backed values instead of case names.
     */
    public function useValues(): bool
    {
        return $this->useValues;
    }
}
