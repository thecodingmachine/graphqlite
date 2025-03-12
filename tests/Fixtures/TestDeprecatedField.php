<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObjectWithDeprecatedField::class)]
#[SourceField(name: 'deprecatedField')]
#[SourceField(name: 'name')]
class TestDeprecatedField
{

}
