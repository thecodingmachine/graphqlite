<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Query;

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
