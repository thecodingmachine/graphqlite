<?php

namespace TheCodingMachine\GraphQLite\Fixtures\StaticTypeMapper\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\StaticTypeMapper\Types\TestLegacyObject;

class TestLegacyController
{
    /**
     * @Query()
     */
    public function getLegacyObject(): TestLegacyObject
    {
        return new TestLegacyObject();
    }
}
