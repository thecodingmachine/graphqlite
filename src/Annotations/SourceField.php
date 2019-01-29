<?php


namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("logged", type = "bool"),
 *   @Attribute("right", type = "TheCodingMachine\GraphQLite\Annotations\Right"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("isId", type = "bool"),
 *   @Attribute("failWith", type = "mixed"),
 * })
 *
 * FIXME: remove idId since outputType="ID" is equivalent
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
    private $outputType;

    /**
     * @var bool
     */
    private $id;

    /**
     * The default value to use if the right is not enforced.
     *
     * @var mixed
     */
    private $failWith;

    /**
     * @var bool
     */
    private $hasFailWith = false;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->name = $attributes['name'] ?? null;
        $this->logged = $attributes['logged'] ?? false;
        $this->right = $attributes['right'] ?? null;
        $this->outputType = $attributes['outputType'] ?? null;
        $this->id = $attributes['isId'] ?? false;
        if (array_key_exists('failWith', $attributes)) {
            $this->failWith = $attributes['failWith'];
            $this->hasFailWith = true;
        }
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
     * The string is the GraphQL output type name.
     *
     * @return string|null
     */
    public function getOutputType(): ?string
    {
        return $this->outputType;
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

    /**
     * Returns the default value to use if the right is not enforced.
     *
     * @return mixed
     */
    public function getFailWith()
    {
        return $this->failWith;
    }

    /**
     * True if a default value is available if a right is not enforced.
     *
     * @return bool
     */
    public function canFailWith(): bool
    {
        return $this->hasFailWith;
    }
}
