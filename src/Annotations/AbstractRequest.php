<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

abstract class AbstractRequest
{
    private ?string $outputType;

    private ?string $name;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [], ?string $name = null, ?string $outputType = null)
    {
        $this->outputType = $outputType ?? $attributes['outputType'] ?? null;
        $this->name       = $name ?? $attributes['name'] ?? null;
    }

    /**
     * Returns the GraphQL return type of the request (as a string).
     * The string can represent the FQCN of the type or an entry in the container resolving to the GraphQL type.
     */
    public function getOutputType(): ?string
    {
        return $this->outputType;
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
