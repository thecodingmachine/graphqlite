<?php

namespace TheCodingMachine\GraphQL\Controllers\Annotations;


/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 */
interface SourceFieldInterface
{
    /**
     * Returns the GraphQL right to be applied to this source field.
     *
     * @return Right|null
     */
    public function getRight(): ?Right;

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     *
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * @return bool
     */
    public function isLogged(): bool;

    /**
     * Returns the GraphQL return type of the request (as a string).
     * The string can represent the FQCN of the type or an entry in the container resolving to the GraphQL type.
     *
     * @return string|null
     */
    public function getOutputType(): ?string;

    /**
     * If the GraphQL type is "ID", isID will return true.
     *
     * @return bool
     */
    public function isId(): bool;
}
