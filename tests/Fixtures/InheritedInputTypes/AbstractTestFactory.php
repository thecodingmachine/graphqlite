<?php

namespace TheCodingMachine\GraphQLite\Fixtures\InheritedInputTypes;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

abstract class AbstractTestFactory
{
    /**
     * @Factory()
     */
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }
}
