<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class TestControllerWithIterableReturnType
{
    /**
     * @Query()
     */
    public function test(): ArrayObject
    {
        return new ArrayObject();
    }
}
