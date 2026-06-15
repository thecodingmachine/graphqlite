<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/** @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html */
interface ObjectTypeHandlerInterface
{
    public function handle(ObjectTypeDescriptor $descriptor): MutableObjectType;
}
