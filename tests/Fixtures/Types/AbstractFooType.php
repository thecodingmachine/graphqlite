<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;

abstract class AbstractFooType
{
    /**
     * @Field()
     * @param TestObject $test
     * @param string $param
     * @return string
     */
    public function customField(TestObject $test, string $param = 'foo'): string
    {
        return $test->getTest().$param;
    }
}
