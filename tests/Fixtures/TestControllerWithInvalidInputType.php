<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use Exception;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class TestControllerWithInvalidInputType
{
    /**
     * @Query()
     */
    public function test(Exception $foo): string
    {
        return 'foo';
    }
}
