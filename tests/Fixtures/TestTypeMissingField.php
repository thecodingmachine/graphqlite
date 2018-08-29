<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\ExposedField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 * @ExposedField(name="notExists")
 */
class TestTypeMissingField
{
}
