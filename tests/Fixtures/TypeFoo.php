<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * A type whose class name does not END with Type
 *
 * @Type(class=TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::class)
 * @SourceField(name="test")
 */
class TypeFoo
{

}