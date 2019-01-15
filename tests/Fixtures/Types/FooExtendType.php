<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use function strtoupper;
use TheCodingMachine\GraphQL\Controllers\Annotations\ExtendType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;

/**
 * @ExtendType(class=TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::class)
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
