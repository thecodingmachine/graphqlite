<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::class)
 * @SourceField(name="test")
 * @SourceField(name="testBool", logged=true)
 * @SourceField(name="testRight", right=@Right(name="FOOBAR"))
 */
class FooType extends AbstractFooType
{
}
