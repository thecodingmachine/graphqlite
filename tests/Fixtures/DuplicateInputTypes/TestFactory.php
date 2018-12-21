<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateInputTypes;

use DateTimeInterface;
use function implode;
use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject2;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObjectWithRecursiveList;

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
