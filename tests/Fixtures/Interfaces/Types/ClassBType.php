<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Interfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Interfaces\ClassB;

#[Type(class: ClassB::class)]
#[SourceField(name: 'bar')]
class ClassBType
{
}
