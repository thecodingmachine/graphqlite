<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 * @SourceField(name="test")
 * @SourceField(name="testBool", logged=true)
 * @SourceField(name="testRight", right=@Right(name="FOOBAR"))
 * @SourceField(name="sibling")
 */
class TestType
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
