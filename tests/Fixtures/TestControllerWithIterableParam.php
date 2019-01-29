<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithIterableParam
{
    /**
     * @Query()
     */
    public function test(ArrayObject $params): string
    {
        return 'foo';
    }
}
