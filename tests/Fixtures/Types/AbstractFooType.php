<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

/**
 * @SourceField(name="test")
 * @SourceField(name="testBool", logged=true)
 * @SourceField(name="testRight", right=@Right(name="FOOBAR"))
 */
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
