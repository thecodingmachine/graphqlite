<?php

namespace TheCodingMachine\GraphQL\Controllers\Types;

use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject2;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObjectWithRecursiveList;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQL\Controllers\GraphQLException;

class ResolvableInputObjectTypeTest extends AbstractQueryProviderTest
{

    public function testResolve(): void
    {
        $inputType = new ResolvableInputObjectType('InputObject',
            $this->getControllerQueryProviderFactory(),
            $this->getTypeMapper(),
            new TestFactory(),
            'myFactory',
            $this->getHydrator(),
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
        $this->expectExceptionMessage("Expected argument 'string' was not provided in GraphQL input type 'InputObject' used in factory 'TheCodingMachine\GraphQL\Controllers\Fixtures\Types\TestFactory::myFactory()'");
        $inputType->resolve([]);
    }

    public function testListResolve(): void
    {
        $inputType = new ResolvableInputObjectType('InputObject2',
            $this->getControllerQueryProviderFactory(),
            $this->getTypeMapper(),
            new TestFactory(),
            'myListFactory',
            $this->getHydrator(),
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
