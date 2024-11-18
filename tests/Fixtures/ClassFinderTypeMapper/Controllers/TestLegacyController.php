<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\ClassFinderTypeMapper\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\ClassFinderTypeMapper\Types\TestLegacyObject;

class TestLegacyController
{
    #[Query]
    public function getLegacyObject(): TestLegacyObject
    {
        return new TestLegacyObject();
    }
}
