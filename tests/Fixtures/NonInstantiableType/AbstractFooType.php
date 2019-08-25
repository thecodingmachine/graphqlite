<?php


namespace TheCodingMachine\GraphQLite\Fixtures\NonInstantiableType;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

/**
 * @Type(class=TheCodingMachine\GraphQLite\Fixtures\TestObject::class)
 * @SourceField(name="test")
 */
abstract class AbstractFooType
{
}
