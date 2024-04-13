<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DuplicateTypes;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

#[Type(class: TestObject::class)]
class TestType
{
}
