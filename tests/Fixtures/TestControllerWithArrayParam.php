<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithArrayParam
{
    /**
     * @return string[]
     */
    #[Query]
    public function test(iterable $params): array
    {
        return $params;
    }
}
