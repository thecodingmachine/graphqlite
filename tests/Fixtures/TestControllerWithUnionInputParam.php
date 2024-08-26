<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use DateTime;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithUnionInputParam
{
    /**
     * @param TestObject|TestObject2 $testObject
     * @return string
     */
    #[Query]
    public function test($testObject): string
    {
        return 'foo';
    }
}
