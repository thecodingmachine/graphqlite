<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
#[SourceField(name: 'test', annotations: [new Right(name: 'FOOBAR'), new FailWith(null)])]
class TestTypeWithFailWith
{
}
