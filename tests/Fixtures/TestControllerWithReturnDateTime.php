<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use DateTime;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithReturnDateTime
{
    /**
     * @Query()
     */
    public function test(): DateTime
    {
        return new DateTime();
    }
}
