<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;
use Throwable;

class TestControllerWithInvalidInputType
{
    #[Query]
    public function test(Throwable $foo): string
    {
        return 'foo';
    }
}
