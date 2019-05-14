<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use DateTimeInterface;
use function implode;
use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithRecursiveList;

class TestFactory
{
    /**
     * @Factory()
     */
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }


    /**
     * @Factory()
     * @param DateTimeInterface $date
     * @param string[] $stringList
     * @param DateTimeInterface[] $dateList
     * @return TestObject2
     */
    public function myListFactory(DateTimeInterface $date, array $stringList, array $dateList): TestObject2
    {
        return new TestObject2($date->format('Y-m-d').'-'.implode('-', $stringList).'-'.count($dateList));
    }

    /**
     * @Decorate("InputObject")
     */
    public function myDecorator(TestObject $testObject, int $int): TestObject
    {
        return $testObject;
    }
}
