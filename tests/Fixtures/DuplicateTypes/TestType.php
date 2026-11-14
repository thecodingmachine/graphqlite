<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\DuplicateTypes;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

#[Type(class: TestObject::class)]
class TestType
{
}
