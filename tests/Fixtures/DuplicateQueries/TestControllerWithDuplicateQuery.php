<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\DuplicateQueries;

use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithDuplicateQuery
{
    #[Query(name: 'duplicateQuery')]
    public function testDuplicateQuery1(): string
    {
        return 'string1';
    }

    #[Query(name: 'duplicateQuery')]
    public function testDuplicateQuery2(): string
    {
        return 'string2';
    }
}
