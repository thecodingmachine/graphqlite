<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

interface InputFieldHandlerInterface
{
    /**
     * Handles a input field descriptor and produces a input field.
     *
     * May call other collaborating code to generate the field.
     */
    public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField;
}
