<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

class TestFactory
{
    #[Factory]
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }
}
