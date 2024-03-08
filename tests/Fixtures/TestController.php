<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use ArrayObject;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\Subscription;
use TheCodingMachine\GraphQLite\Types\ID;

class TestController
{
    /**
     * @param TestObject[] $list
     */
    #[Query]
    public function test(
        int $int,
        array $list,
        ?bool $boolean,
        ?float $float,
        ?\DateTimeImmutable $dateTimeImmutable,
        ?\DateTimeInterface $dateTime,
        string $withDefault = 'default',
        ?string $string = null,
        ID $id = null,
        TestEnum $enum = null,
    ): TestObject
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

    #[Query]
    #[HideIfUnauthorized]
    #[Logged]
    public function testLogged(): TestObject
    {
        return new TestObject('foo');
    }

    #[Query]
    #[Right(name: "CAN_FOO")]
    #[HideIfUnauthorized]
    public function testRight(): TestObject
    {
        return new TestObject('foo');
    }

    #[Query(outputType: 'ID')]
    public function testFixReturnType(): TestObject
    {
        return new TestObject('foo');
    }

    #[Query(name: 'nameFromAnnotation')]
    public function testNameFromAnnotation(): TestObject
    {
        return new TestObject('foo');
    }

    /**
     * @return ArrayObject|TestObject[]
     */
    #[Query(name: 'arrayObject')]
    public function testArrayObject(): ArrayObject
    {
        return new ArrayObject([]);
    }

    /**
     * @return ArrayObject<TestObject>
     */
    #[Query(name: 'arrayObjectGeneric')]
    public function testArrayObjectGeneric(): ArrayObject
    {
        return new ArrayObject([]);
    }

    /**
     * @return iterable|TestObject[]
     */
    #[Query(name: 'iterable')]
    public function testIterable(): iterable
    {
        return array();
    }

    /**
     * @return iterable<TestObject>
     */
    #[Query(name: 'iterableGeneric')]
    public function testIterableGeneric(): iterable
    {
        return array();
    }

    /**
     * @return TestObject|TestObject2
     */
    #[Query(name: 'union')]
    public function testUnion()
    {
        return new TestObject2('foo');
    }

    #[Query(outputType: '[ID!]!')]
    public function testFixComplexReturnType(): array
    {
        return ['42'];
    }

    #[Mutation]
    public function testVoid(): void
    {
    }

    #[Mutation]
    public function testReturn(TestObject $testObject): TestObject
    {
        return $testObject;
    }

    #[Subscription(outputType: 'ID')]
    public function testSubscribe(): void
    {}

    #[Subscription(outputType: 'ID')]
    public function testSubscribeWithInput(TestObject $testObject): void
    {}
}
