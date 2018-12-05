<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class TestControllerWithArrayParam
{
    /**
     * @Query()
     */
    public function test(array $params): array
    {
        return $params;
    }
}
