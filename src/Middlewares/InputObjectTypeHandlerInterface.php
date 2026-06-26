<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/** @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html */
interface InputObjectTypeHandlerInterface
{
    public function handle(InputObjectTypeDescriptor $descriptor): MutableInputObjectType;
}
