<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassA;

/**
 * @Type(class=ClassA::class)
 * @SourceField(name="foo")
 */
class ClassAType
{

}