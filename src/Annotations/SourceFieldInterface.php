<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 */
interface SourceFieldInterface
{
    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     */
    public function getName(): ?string;

    /**
     * Returns the GraphQL return type of the request (as a string).
     * The string is the GraphQL output type name.
     */
    public function getOutputType(): ?string;

    public function getAnnotations(): MiddlewareAnnotations;
}
