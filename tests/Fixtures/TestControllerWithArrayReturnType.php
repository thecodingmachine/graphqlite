<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

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
