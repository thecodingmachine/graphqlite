<?php

namespace TheCodingMachine\GraphQLite\Types;

use DateTimeImmutable;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use stdClass;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithRecursiveList;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Mappers\Parameters\HardCodedParameter;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class ResolvableMutableInputObjectTypeTest extends AbstractQueryProviderTest
{

    public function testResolve(): void
    {
        $inputType = new ResolvableMutableInputObjectType('InputObject',
            $this->getFieldsBuilder(),
            new TestFactory(),
            'myFactory',
            'my comment',
            false);

        $this->assertSame('InputObject', $inputType->name);
        $inputType->freeze();
        $this->assertCount(2, $inputType->getFields());
        $this->assertSame('my comment', $inputType->description);

        $resolveInfo = $this->createMock(ResolveInfo::class);

        $obj = $inputType->resolve(new stdClass(), ['string' => 'foobar', 'bool' => false], null, $resolveInfo);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(false, $obj->isTestBool());

        $obj = $inputType->resolve(new stdClass(), ['string' => 'foobar'], null, $resolveInfo);
        $this->assertInstanceOf(TestObject::class, $obj);
        $this->assertSame('foobar', $obj->getTest());
        $this->assertSame(true, $obj->isTestBool());

        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage("Expected argument 'string' was not provided in GraphQL input type 'InputObject' used in factory 'TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory::myFactory()'");
        $inputType->resolve(new stdClass(), [], null, $resolveInfo);
    }

    public function testDecoratorMissingArgumentException(): void
    {
        $testFactory = new TestFactory();
        $inputType = new ResolvableMutableInputObjectType('InputObject',
            $this->getFieldsBuilder(),
            $testFactory,
            'myFactory',
            'my comment',
            true);

        $inputType->decorate([$testFactory, 'myDecorator']);
        $this->assertFalse($inputType->isInstantiableWithoutParameters());

        $resolveInfo = $this->createMock(ResolveInfo::class);

        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage("Expected argument 'int' was not provided in GraphQL input type 'InputObject' used in decorator 'TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory::myDecorator()'");
        $inputType->resolve(new stdClass(), ['string' => 'foobar'], null, $resolveInfo);
    }

    public function testDecoratorDoesNotModifyInstantiableWithoutParameters(): void
    {
        $testFactory = new TestFactory();
        $inputType = new ResolvableMutableInputObjectType('InputObject',
            $this->getFieldsBuilder(),
            $testFactory,
            'myFactory',
            'my comment',
            false);

        $inputType->decorate([$testFactory, 'myDecorator']);
        $this->assertFalse($inputType->isInstantiableWithoutParameters());
    }

    public function testListResolve(): void
    {
        $inputType = new ResolvableMutableInputObjectType('InputObject2',
            $this->getFieldsBuilder(),
            new TestFactory(),
            'myListFactory',
            null,
            false);

        $obj = $inputType->resolve(new stdClass(), ['date' => '2018-12-25', 'stringList' =>
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

    public function testExceptions(): void
    {
        $fieldsBuilder = $this->createMock(FieldsBuilder::class);

        $fieldsBuilder->method('getParameters')->willReturn([
            new class implements ParameterInterface {
                public function resolve(?object $source, array $args, $context, ResolveInfo $info)
                {
                    throw new Error('boum');
                }
            },
            new class implements ParameterInterface {
                public function resolve(?object $source, array $args, $context, ResolveInfo $info)
                {
                    throw new Error('boum');
                }
            }
        ]);

        $inputType = new ResolvableMutableInputObjectType('InputObject2',
            $fieldsBuilder,
            new TestFactory(),
            'myListFactory',
            null,
            false);

        $this->expectException(GraphQLAggregateException::class);
        $obj = $inputType->resolve(new stdClass(), ['date' => '2018-12-25', 'stringList' =>
            [
                'foo',
                'bar'
            ],
            'dateList' => [
                '2018-12-25'
            ]], null, $this->createMock(ResolveInfo::class));
    }

    public function testExceptionsInDecorator(): void
    {
        $fieldsBuilder = $this->createMock(FieldsBuilder::class);

        $fieldsBuilder->method('getParameters')->willReturn([
            new HardCodedParameter(new DateTimeImmutable('now')),
            new HardCodedParameter([]),
            new HardCodedParameter([]),
            ]);


        $fieldsBuilder->method('getParametersForDecorator')->willReturn([
            new class implements ParameterInterface {
                public function resolve(?object $source, array $args, $context, ResolveInfo $info)
                {
                    throw new Error('boum');
                }
            },
            new class implements ParameterInterface {
                public function resolve(?object $source, array $args, $context, ResolveInfo $info)
                {
                    throw new Error('boum');
                }
            }
        ]);

        $testFactory = new TestFactory();
        $inputType = new ResolvableMutableInputObjectType('InputObject2',
            $fieldsBuilder,
            $testFactory,
            'myListFactory',
            null,
            false);

        $inputType->decorate([$testFactory, 'myDecorator']);

        $this->expectException(GraphQLAggregateException::class);
        $obj = $inputType->resolve(new stdClass(), ['date' => '2018-12-25', 'stringList' =>
            [
                'foo',
                'bar'
            ],
            'dateList' => [
                '2018-12-25'
            ]], null, $this->createMock(ResolveInfo::class));
    }

}
