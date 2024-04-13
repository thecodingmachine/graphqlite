<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use DateTimeInterface;
use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;

use function count;
use function implode;

class TestFactory
{
    #[Factory]
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }

    /**
     * @param string[] $stringList
     * @param DateTimeInterface[] $dateList
     */
    #[Factory]
    public function myListFactory(DateTimeInterface $date, array $stringList, array $dateList): TestObject2
    {
        return new TestObject2($date->format('Y-m-d') . '-' . implode('-', $stringList) . '-' . count($dateList));
    }

    #[Decorate('InputObject')]
    public function myDecorator(TestObject $testObject, int $int): TestObject
    {
        return $testObject;
    }
}
