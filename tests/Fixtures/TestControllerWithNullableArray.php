<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithNullableArray
{
    /**
     * @Query()
     * @param  array<int|null> $params
     * @return array<int|null>
     */
    public function test(array $params): array
    {
        return $params;
    }
}
