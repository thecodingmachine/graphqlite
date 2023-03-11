<?php

namespace TheCodingMachine\GraphQLite\Fixtures\DuplicateQueriesInTwoControllers;

use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithDuplicateQuery1
{
    /**
     * @Query()
     */
    public function duplicateQuery(): string
    {
        return 'string1';
    }
}
