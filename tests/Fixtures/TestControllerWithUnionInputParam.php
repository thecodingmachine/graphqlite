<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use DateTime;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithUnionInputParam
{
    /**
     * @Query()
     * @param TestObject|TestObject2 $testObject
     * @return string
     */
    public function test($testObject): string
    {
        return 'foo';
    }
}
