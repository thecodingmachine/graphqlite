<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;


abstract class AbstractRequest
{
    /**
     * @var string|null
     */
    private $returnType;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->returnType = $attributes['returnType'] ?? null;
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
