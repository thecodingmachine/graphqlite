<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use DateTime;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithParamDateTime
{
    #[Query]
    public function test(DateTime $dateTime): string
    {
        return 'foo';
    }
}
