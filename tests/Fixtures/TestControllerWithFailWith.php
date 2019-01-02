<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQL\Controllers\Annotations\FailWith;
use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;

class TestControllerWithFailWith
{
    /**
     * @Query
     * @Logged
     * @FailWith(null)
     */
    public function testFailWith(): TestObject
    {
        return new TestObject('foo');
    }
}
