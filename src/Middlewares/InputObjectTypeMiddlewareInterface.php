<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * A middleware in the {@see InputObjectTypeMiddlewarePipe} that decorates
 * {@see MutableInputObjectType} instances right after they're built (both `#[Input]` and
 * `#[Factory]` types).
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface InputObjectTypeMiddlewareInterface
{
    public function process(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType;
}
