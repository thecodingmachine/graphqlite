<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithArrayReturnType
{

    #[Query]
    public function test(): array
    {
        return [];
    }
}
