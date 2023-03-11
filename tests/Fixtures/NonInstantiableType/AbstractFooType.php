<?php

namespace TheCodingMachine\GraphQLite\Fixtures\NonInstantiableType;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TheCodingMachine\GraphQLite\Fixtures\TestObject::class)
 * @SourceField(name="test")
 */
abstract class AbstractFooType
{
}
