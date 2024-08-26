<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Security;

class TestControllerWithBadSecurity
{
    #[Query]
    #[Security('this is not valid expression language')]
    public function testBadSecurity(): TestObject
    {
        return new TestObject('foo');
    }
}
