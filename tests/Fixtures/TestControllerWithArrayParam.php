<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithArrayParam
{
    /**
     * @Query()
     */
    public function test(iterable $params): array
    {
        return $params;
    }
}
