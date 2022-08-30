<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

/**
 * Used for validating InputTypes
 * An implementation of this interface can be registered with the SchemaFactory.
 */
interface InputTypeValidatorInterface
{
    /**
     * Checks to see if the Validator is currently enabled.
     */
    public function isEnabled(): bool;

    /**
     * Performs the validation of the InputType.
     *
     * @param object $input     The input type object to validate
     */
    public function validate(object $input): void;
}
