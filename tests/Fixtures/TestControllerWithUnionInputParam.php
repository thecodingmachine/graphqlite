<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithUnionInputParam
{
    /**
     * @Query()
     * @param TestObject|TestObject2 $testObject
     */
    public function test($testObject): string
    {
        return 'foo';
    }
}
