<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;

class TestController
{
    /**
     * @Query
     * @param int $int
     * @param null|string $string
     * @param TestObject[] $list
     * @param bool|null $boolean
     * @param float|null $float
     * @param \DateTimeImmutable|null $dateTimeImmutable
     * @param \DateTime|null $dateTime
     * @return TestObject
     */
    public function test(int $int, ?string $string, array $list, ?bool $boolean, ?float $float, ?\DateTimeImmutable $dateTimeImmutable, ?\DateTimeInterface $dateTime): TestObject
    {
        $str = '';
        foreach ($list as $test) {
            if (!$test instanceof TestObject) {
                throw new \RuntimeException('TestObject instance expected.');
            }
            $str .= $test->getTest();
        }
        return new TestObject($string.$int.$str.($boolean?'true':'false').$float.$dateTimeImmutable->format('YmdHis').$dateTime->format('YmdHis'));
    }

    /**
     * @Mutation
     * @param TestObject $testObject
     * @return TestObject
     */
    public function mutation(TestObject $testObject): TestObject
    {
        return $testObject;
    }

    /**
     * @Query
     * @Logged
     */
    public function testLogged(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @Query
     * @Right(name="CAN_FOO")
     */
    public function testRight(): TestObject
    {
        return new TestObject('foo');
    }
}
