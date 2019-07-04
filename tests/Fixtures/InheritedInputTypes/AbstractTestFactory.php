<?php


namespace TheCodingMachine\GraphQLite\Fixtures\InheritedInputTypes;

use DateTimeInterface;
use function implode;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithRecursiveList;

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
