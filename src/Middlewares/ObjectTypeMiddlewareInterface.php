<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/**
 * A middleware in the {@see ObjectTypeMiddlewarePipe} used to decorate {@see MutableObjectType}
 * instances right after construction.
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface ObjectTypeMiddlewareInterface
{
    public function process(ObjectTypeDescriptor $descriptor, ObjectTypeHandlerInterface $next): MutableObjectType;
}
