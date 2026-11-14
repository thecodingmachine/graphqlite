<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

#[Type(class: TestObject::class)]
class FooType extends AbstractFooType
{
}
