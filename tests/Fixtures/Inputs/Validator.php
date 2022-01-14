<?php

declare(strict_types = 1);

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Types\InputTypeValidatorInterface;

/**
 * Test validator, we just throw an Exception on validation to test that this validator is called.
 *
 * @author Jacob Thomason <jacob@thomason.xxx>
 */
class Validator implements InputTypeValidatorInterface
{

    private bool $isEnabled;

    public function __construct(bool $isEnabled = true)
    {
        $this->isEnabled = $isEnabled;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function validate(object $input): void
    {
        throw new ValidationException('Validation failed');
    }
}
