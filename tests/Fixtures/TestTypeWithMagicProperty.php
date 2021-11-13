<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\FromSourceFieldsInterface;

/**
 * @Type()
 * @MagicField(name="foo", outputType="String!", description="Test MagicField description")
 */
class TestTypeWithMagicProperty
{
    public function __get(string $var)
    {
        return 'foo';
    }
}
