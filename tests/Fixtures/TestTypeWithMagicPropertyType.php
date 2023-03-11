<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestTypeWithMagicProperty::class)
 * @MagicField(name="foo", phpType="string")
 */
class TestTypeWithMagicPropertyType
{
}
