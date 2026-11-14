<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject2;

class UnionOutputType
{
    public function objectUnion(): TestObject|TestObject2 {
        return new TestObject((''));
    }
    public function nullableObjectUnion(): TestObject|TestObject2|null {
        return new TestObject((''));
    }
}
