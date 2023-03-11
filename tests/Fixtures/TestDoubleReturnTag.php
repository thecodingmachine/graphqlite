<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestDoubleReturnTag
{
    /**
     * @Field()
     * @return string
     */
    public function test(): array
    {
        return [];
    }
}
