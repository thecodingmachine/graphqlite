<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Security;

class TestControllerWithBadSecurity
{
    /**
     * @Query
     * @Security("this is not valid expression language")
     */
    public function testBadSecurity(): TestObject
    {
        return new TestObject('foo');
    }
}
