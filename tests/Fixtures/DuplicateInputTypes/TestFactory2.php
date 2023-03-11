<?php

namespace TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

class TestFactory2
{
    /**
     * @Factory()
     */
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }
}
