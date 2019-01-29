<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

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
