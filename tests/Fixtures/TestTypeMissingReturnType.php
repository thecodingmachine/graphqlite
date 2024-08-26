<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObjectMissingReturnType::class)]
#[SourceField(name: 'test')]
class TestTypeMissingReturnType
{
}
