<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 * @SourceField(name="test", right=@Right(name="FOOBAR"), failWith=null)
 */
class TestTypeWithFailWith
{
}
