<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithIterableParam
{
    #[Query]
    public function test(ArrayObject $params): string
    {
        return 'foo';
    }
}
