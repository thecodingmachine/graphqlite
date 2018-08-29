<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;

/**
 * ExposedFields are fields that are directly exposed from the base object into GraphQL.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("logged", type = "bool"),
 *   @Attribute("right", type = Right::class),
 *   @Attribute("returnType", type = "string"),
 * })
 */
class ExposedField
{
    /**
     * @var Right|null
     */
    private $right;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool
     */
    private $logged;

    /**
     * @var string|null
     */
    private $returnType;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->name = $attributes['name'] ?? null;
        $this->logged = $attributes['logged'] ?? false;
        $this->right = $attributes['right'] ?? null;
        $this->returnType = $attributes['returnType'] ?? null;
    }

    /**
     * Returns the GraphQL right to be applied to this exposed field.
     *
     * @return Right|null
     */
    public function getRight(): ?Right
    {
        return $this->right;
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isLogged(): bool
    {
        return $this->logged;
    }

    /**
     * Returns the GraphQL return type of the request (as a string).
     * The string can represent the FQCN of the type or an entry in the container resolving to the GraphQL type.
     *
     * @return string|null
     */
    public function getReturnType(): ?string
    {
        return $this->returnType;
    }
}
