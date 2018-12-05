<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use Exception;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

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
