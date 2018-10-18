<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 * @SourceField(name="test", isId=true)
 */
class TestTypeId
{
}
