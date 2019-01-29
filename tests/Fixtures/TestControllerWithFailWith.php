<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;

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
