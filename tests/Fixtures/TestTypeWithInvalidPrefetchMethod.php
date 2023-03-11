<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestTypeWithInvalidPrefetchMethod
{
    /**
     * @Field(prefetchMethod="notExists")
     */
    public function test(): string
    {
        return 'foo';
    }
}
