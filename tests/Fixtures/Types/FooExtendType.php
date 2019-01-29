<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

/**
 * @ExtendType(class=TheCodingMachine\GraphQLite\Fixtures\TestObject::class)
 */
class FooExtendType
{
    /**
     * @Field()
     * @param TestObject $test
     * @return string
     */
    public function customExtendedField(TestObject $test): string
    {
        return strtoupper($test->getTest());
    }
}
