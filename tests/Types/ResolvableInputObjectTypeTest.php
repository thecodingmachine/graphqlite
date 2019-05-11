<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithRecursiveList;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;

class ResolvableInputObjectTypeTest extends AbstractQueryProviderTest
{

    public function testResolve(): void
    {
        $inputType = new ResolvableMutableInputObjectType('InputObject',
            $this->getFieldsBuilder(),
            new TestFactory(),
            'myFactory',
            $this->getArgumentResolver(),
            'my comment');

        $this->assertSame('InputObject', $inputType->name);
        $inputType->freeze();
        $this->assertCount(2, $inputType->getFields());
        $this->assertSame('my comment', $inputType->description);

        $resolveInfo = $this->createMock(ResolveInfo::class);

        $obj = $inputType->resolve(null, ['string' => 'foobar', 'bool' => false], null, $resolveInfo);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(false, $obj->isTestBool());

        $obj = $inputType->resolve(null, ['string' => 'foobar'], null, $resolveInfo);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(true, $obj->isTestBool());

        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage("Expected argument 'string' was not provided in GraphQL input type 'InputObject' used in factory 'TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory::myFactory()'");
        $inputType->resolve(null, [], null, $resolveInfo);
    }

    public function testListResolve(): void
    {
        $inputType = new ResolvableMutableInputObjectType('InputObject2',
            $this->getFieldsBuilder(),
            new TestFactory(),
            'myListFactory',
            $this->getArgumentResolver(),
            null);

        $obj = $inputType->resolve(null, ['date' => '2018-12-25', 'stringList' =>
            [
                'foo',
                'bar'
            ],
            'dateList' => [
                '2018-12-25'
            ]], null, $this->createMock(ResolveInfo::class));
        $this->assertInstanceOf(TestObject2::class, $obj);
        $this->assertSame('2018-12-25-foo-bar-1', $obj->getTest2());
    }
}
