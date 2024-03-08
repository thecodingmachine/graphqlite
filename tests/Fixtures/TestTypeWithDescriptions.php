<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestTypeWithDescriptions
{
    /**
     * @Field()
     * @param TestObject $test
     * @param string $arg1 Test argument description
     * @return string
     */
    public function customField(TestObject $test, string $arg1): string
    {
        return $test->getTest().$arg1;
    }
}
