<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TestObjectMissingReturnType::class)
 * @SourceField(name="test")
 */
class TestTypeMissingReturnType
{
}
