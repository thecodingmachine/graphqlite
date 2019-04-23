<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestFieldBadOutputType
{
    /**
     * @Field(outputType="[NotExists]")
     */
    public function test(): array
    {
        return [];
    }
}
