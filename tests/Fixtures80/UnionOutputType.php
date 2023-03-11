<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures80;

use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;

class UnionOutputType
{
    public function objectUnion(): TestObject|TestObject2
    {
        return new TestObject('');
    }

    public function nullableObjectUnion(): TestObject|TestObject2|null
    {
        return new TestObject('');
    }
}
