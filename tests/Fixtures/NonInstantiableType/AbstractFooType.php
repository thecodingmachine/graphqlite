<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\NonInstantiableType;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

#[Type(class: TestObject::class)]
#[SourceField(name: 'test')]
abstract class AbstractFooType
{
}
