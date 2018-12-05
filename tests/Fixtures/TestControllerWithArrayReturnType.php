<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class TestControllerWithArrayReturnType
{
    /**
     * @Query()
     */
    public function test(): array
    {
        return [];
    }
}
