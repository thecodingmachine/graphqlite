<?php

namespace TheCodingMachine\GraphQLite\Annotations;


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
     * The string is the GraphQL output type name.
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

    /**
     * Returns the default value to use if the right is not enforced.
     *
     * @return mixed
     */
    public function getFailWith();

    /**
     * True if a default value is available if a right is not enforced.
     *
     * @return bool
     */
    public function canFailWith();
}
