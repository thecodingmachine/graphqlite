<?php

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\DuplicateQueriesInTwoControllers;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithDuplicateQuery2
{
    #[Query]
    public function duplicateQuery(): string
    {
        return 'string2';
    }
}
