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
class TestTypeWithPrefetchMethods
{
    /**
     * @Field(prefetchMethod="prefetch1")
     */
    public function test(
        TestObject $testObject,
        $prefetchedData1,
        #[Prefetch('prefetch2')] $prefetchedData2,
        int $arg3,
        #[Prefetch('prefetch3')] $prefetchedData3,
    ): string
    {
        return $prefetchedData1[0].$arg3;
    }

    public function prefetch1(iterable $testObjects, string $arg1)
    {
        return [$arg1];
    }

    public static function prefetch2(iterable $testObjects, string $arg2)
    {
        return [$arg2];
    }

    public static function prefetch3(iterable $testObjects, string $arg4)
    {
        return [$arg4];
    }
}
