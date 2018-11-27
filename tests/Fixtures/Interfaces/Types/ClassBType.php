<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassB;

/**
 * @Type(class=ClassB::class)
 * @SourceField(name="bar")
 */
class ClassBType
{

}
