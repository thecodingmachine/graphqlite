<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\InputObjectField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

/**
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface InputFieldHandlerInterface
{
    /**
     * Handles a input field descriptor and produces a input field.
     *
     * May call other collaborating code to generate the field.
     */
    public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputObjectField;
}
