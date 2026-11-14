<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\NonInstantiableType;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

#[Type(class: TestObject::class)]
#[SourceField(name: 'test')]
abstract class AbstractFooType
{
}
