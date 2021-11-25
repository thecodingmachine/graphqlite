<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * The EnumType annotation is useful to change the name of the generated "enum" type.
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
    /** @var string|null */
    private $name;

    /** @var bool */
    private $useValues;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [], ?string $name = null, ?bool $useValues = null)
    {
        $this->name = $name ?? $attributes['name'] ?? null;
        $this->useValues = $useValues ?? $attributes['useValues'] ?? false;
    }

    /**
     * Returns the GraphQL name for this type.
     */
    public function getName(): ?string
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
