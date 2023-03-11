<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

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
