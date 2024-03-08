<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestTypeWithInvalidPrefetchMethod
{
    /**
     * @Field()
     */
    public function test(object $source, #[Prefetch('notExists')] $data): string
    {
        return 'foo';
    }
}
