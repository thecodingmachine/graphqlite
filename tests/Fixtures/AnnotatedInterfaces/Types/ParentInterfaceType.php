<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=ParentInterface::class)
 * @SourceField(name="parentValue")
 */
class ParentInterfaceType
{
}