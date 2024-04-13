<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

#[Type(class: TestObject::class)]
class FooType extends AbstractFooType
{
}
