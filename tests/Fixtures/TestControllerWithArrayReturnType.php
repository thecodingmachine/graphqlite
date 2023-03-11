<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

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
