<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Types\ID;

class TestController
{
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
     * @param int $int
     * @param TestObject[] $list
     * @param bool|null $boolean
     * @param float|null $float
     * @param \DateTimeImmutable|null $dateTimeImmutable
     * @param \DateTimeInterface|null $dateTime
     * @param string $withDefault
     * @param null|string $string
     * @param ID|null $id
     * @param TestEnum $enum
     * @return TestObject
     */
    public function test(int $int, array $list, ?bool $boolean, ?float $float, ?\DateTimeImmutable $dateTimeImmutable, ?\DateTimeInterface $dateTime, string $withDefault = 'default', ?string $string = null, ID $id = null, TestEnum $enum = null): TestObject
    {
        $str = '';
        foreach ($list as $test) {
            if (!$test instanceof TestObject) {
                throw new \RuntimeException('TestObject instance expected.');
            }
            $str .= $test->getTest();
        }
        return new TestObject($string.$int.$str.($boolean?'true':'false').$float.$dateTimeImmutable->format('YmdHis').$dateTime->format('YmdHis').$withDefault.($id !== null ? $id->val() : '').$enum->getValue());
    }

    /**
     * @Query
     * @Logged
     * @HideIfUnauthorized()
     */
    public function testLogged(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @Query
     * @Right(name="CAN_FOO")
     * @HideIfUnauthorized()
     */
    public function testRight(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @Query(outputType="ID")
     */
    public function testFixReturnType(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @Query(name="nameFromAnnotation")
     */
    public function testNameFromAnnotation(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @Query(name="arrayObject")
     * @return ArrayObject|TestObject[]
     */
    public function testArrayObject(): ArrayObject
    {
        return new ArrayObject([]);
    }

    /**
     * @Query(name="iterable")
     * @return iterable|TestObject[]
     */
    public function testIterable(): iterable
    {
        return array();
    }

    /**
     * @Query(name="union")
     * @return TestObject|TestObject2
     */
    public function testUnion()
    {
        return new TestObject2('foo');
    }

    /**
     * @Query(outputType="[ID!]!")
     */
    public function testFixComplexReturnType(): array
    {
        return ['42'];
    }
}
