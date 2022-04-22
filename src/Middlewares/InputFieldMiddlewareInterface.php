<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

/**
 * A middleware use to process annotations when evaluating a input field
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface InputFieldMiddlewareInterface
{
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField;
}
