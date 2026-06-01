<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * A middleware in the {@see InputObjectTypeMiddlewarePipe} used to decorate
 * {@see MutableInputObjectType} instances right after construction. Covers both `#[Input]` classes
 * and `#[Factory]`-produced types.
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface InputObjectTypeMiddlewareInterface
{
    public function process(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType;
}
