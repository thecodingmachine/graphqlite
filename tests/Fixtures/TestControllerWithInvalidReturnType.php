<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithInvalidReturnType
{
    /**
     * @Query()
     */
    public function test(): Exception
    {
        return new Exception('foo');
    }
}
