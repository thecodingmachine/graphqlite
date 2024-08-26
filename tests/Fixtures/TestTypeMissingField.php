<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
#[SourceField(name: 'notExists')]
class TestTypeMissingField
{
}
