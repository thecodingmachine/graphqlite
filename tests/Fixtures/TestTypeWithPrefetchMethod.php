<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type(class=TestObject::class)
 */
class TestTypeWithPrefetchMethod
{
    /**
     * @Field(prefetchMethod="prefetch")
     */
    public function test(TestObject $testObject, $prefetchedData, int $int): string
    {
        return $prefetchedData[0].$int;
    }

    public function prefetch(iterable $testObjects, string $string)
    {
        return [$string];
    }
}
