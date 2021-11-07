<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\MagicField;

/**
 * @Type(class=TestTypeWithMagicProperty::class)
 * @MagicField(name="foo", phpType="string")
 */
class TestTypeWithMagicPropertyType
{
}
