<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;

/**
 * @Type(class=TestObject::class)
 */
class TestFieldBadInputType
{
    /**
     * @Field()
     * @UseInputType(for="$input", inputType="[NotExists]")
     */
    public function testInput(TestObject $obj, $input): string
    {
        return 'foo';
    }
}
