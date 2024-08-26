<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithNullableArray
{
    /**
     * @param array<int|null> $params
     * @return array<int|null>
     */
    #[Query]
    public function test(array $params): array
    {
        return $params;
    }
}
