<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * A type whose class name does not END with Type
 *
 * @Type(class=TheCodingMachine\GraphQLite\Fixtures\TestObject::class)
 * @SourceField(name="test")
 */
class TypeFoo
{

}