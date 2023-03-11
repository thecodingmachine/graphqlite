<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

use function strtoupper;

/**
 * @ExtendType(class=TheCodingMachine\GraphQLite\Fixtures\TestObject::class)
 */
class FooExtendType
{
    /**
     * @Field()
     */
    public function customExtendedField(TestObject $test): string
    {
        return strtoupper($test->getTest());
    }
}
