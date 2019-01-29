<?php


namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Factories are methods used to declare GraphQL input types.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string")
 * })
 */
class Factory
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->name = $attributes['name'] ?? null;
    }

    /**
     * Returns the name of the GraphQL input type.
     * If not specified, the name of the method should be used instead.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
