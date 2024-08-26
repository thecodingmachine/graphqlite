<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerNoReturnType
{
    #[Query]
    public function test()
    {
        return 'foo';
    }
}
