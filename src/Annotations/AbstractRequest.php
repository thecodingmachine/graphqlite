<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;


abstract class AbstractRequest
{
    /**
     * @var string|null
     */
    private $returnType;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->returnType = $attributes['returnType'] ?? null;
        $this->name = $attributes['name'] ?? null;
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
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
