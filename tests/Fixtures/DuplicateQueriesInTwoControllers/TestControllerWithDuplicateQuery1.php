<?php

namespace TheCodingMachine\GraphQLite\Fixtures\DuplicateQueriesInTwoControllers;

use ArrayObject;
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
