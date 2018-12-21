<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

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
}
