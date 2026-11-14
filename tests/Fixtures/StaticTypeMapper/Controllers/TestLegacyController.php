<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\StaticTypeMapper\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Tests\Fixtures\StaticTypeMapper\Types\TestLegacyObject;

class TestLegacyController
{
    #[Query]
    public function getLegacyObject(): TestLegacyObject
    {
        return new TestLegacyObject();
    }
}
