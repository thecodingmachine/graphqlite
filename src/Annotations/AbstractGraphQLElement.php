<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Shared base for every attribute that declares an invokable GraphQL schema element with a
 * return type — {@see Query}, {@see Mutation}, {@see Subscription}, and {@see Field}. Each of
 * those attributes inherits a GraphQL-level name, an explicit return type override, and a
 * schema description from this class.
 */
abstract class AbstractGraphQLElement
{
    private string|null $outputType;

    private string|null $name;

    private string|null $description;

    /** @param mixed[] $attributes */
    public function __construct(
        array $attributes = [],
        string|null $name = null,
        string|null $outputType = null,
        string|null $description = null,
    ) {
        $this->outputType  = $outputType ?? $attributes['outputType'] ?? null;
        $this->name        = $name ?? $attributes['name'] ?? null;
        $this->description = $description ?? $attributes['description'] ?? null;
    }

    /**
     * Returns the GraphQL return type for this schema element (as a string).
     * The string can represent the FQCN of the type or an entry in the container resolving to the GraphQL type.
     */
    public function getOutputType(): string|null
    {
        return $this->outputType;
    }

    /**
     * Returns the GraphQL name of the query/mutation/subscription/field.
     * If not specified, the name of the PHP method is used instead.
     */
    public function getName(): string|null
    {
        return $this->name;
    }

    /**
     * Returns the explicit description for this schema element, or null if none was provided.
     *
     * A null return means "no explicit description" and the schema builder may fall back to the
     * docblock summary (if docblock descriptions are enabled on the SchemaFactory). An explicit
     * empty string blocks the docblock fallback and produces an empty description.
     */
    public function getDescription(): string|null
    {
        return $this->description;
    }
}
