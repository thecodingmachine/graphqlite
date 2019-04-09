<?php

namespace TheCodingMachine\GraphQLite\Types;

use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithRecursiveList;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\GraphQLException;

class ResolvableInputObjectTypeTest extends AbstractQueryProviderTest
{

    public function testResolve(): void
    {
        $inputType = new ResolvableInputObjectType('InputObject',
            $this->getControllerQueryProviderFactory(),
            $this->getTypeMapper(),
            new TestFactory(),
            'myFactory',
            $this->getArgumentResolver(),
            'my comment');

        $this->assertSame('InputObject', $inputType->name);
        $this->assertCount(2, $inputType->getFields());
        $this->assertSame('my comment', $inputType->description);

        $obj = $inputType->resolve(['string' => 'foobar', 'bool' => false]);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(false, $obj->isTestBool());

        $obj = $inputType->resolve(['string' => 'foobar']);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(true, $obj->isTestBool());

        $this->expectException(GraphQLException::class);
        $this->expectExceptionMessage("Expected argument 'string' was not provided in GraphQL input type 'InputObject' used in factory 'TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory::myFactory()'");
        $inputType->resolve([]);
    }

    public function testListResolve(): void
    {
        $inputType = new ResolvableInputObjectType('InputObject2',
            $this->getControllerQueryProviderFactory(),
            $this->getTypeMapper(),
            new TestFactory(),
            'myListFactory',
            $this->getArgumentResolver(),
            null);

        $obj = $inputType->resolve(['date' => '2018-12-25', 'stringList' =>
            [
                'foo',
                'bar'
            ],
            'dateList' => [
                '2018-12-25'
            ]]);
        $this->assertInstanceOf(TestObject2::class, $obj);
        $this->assertSame('2018-12-25-foo-bar-1', $obj->getTest2());
    }
}
