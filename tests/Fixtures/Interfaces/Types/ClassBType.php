<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassB;

/**
 * @Type(class=ClassB::class)
 * @SourceField(name="bar")
 */
class ClassBType
{

}
