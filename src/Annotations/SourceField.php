<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;

/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("logged", type = "bool"),
 *   @Attribute("right", type = "TheCodingMachine\GraphQL\Controllers\Annotations\Right"),
 *   @Attribute("returnType", type = "string"),
 *   @Attribute("isId", type = "bool"),
 * })
 */
class SourceField implements SourceFieldInterface
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
     * @var bool
     */
    private $id;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->name = $attributes['name'] ?? null;
        $this->logged = $attributes['logged'] ?? false;
        $this->right = $attributes['right'] ?? null;
        $this->returnType = $attributes['returnType'] ?? null;
        $this->id = $attributes['isId'] ?? false;
    }

    /**
     * Returns the GraphQL right to be applied to this source field.
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

    /**
     * If the GraphQL type is "ID", isID will return true.
     *
     * @return bool
     */
    public function isId(): bool
    {
        return $this->id;
    }
}
