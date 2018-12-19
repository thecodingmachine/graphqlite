<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;

class TestFactory
{
    /**
     * @Factory()
     */
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }
}
