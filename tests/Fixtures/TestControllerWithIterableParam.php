<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

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
