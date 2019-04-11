<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;

// An abstract class to test that the GlobControllerQueryProvider does not try anything with it.
abstract class AbstractTestController
{
    /**
     * @Query()
     */
    public function test(): string
    {
        return 'foo';
    }
}
