<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

class StaticTypeMapperTest extends AbstractQueryProviderTest
{
    /**
     * @var StaticTypeMapper
     */
    private $typeMapper;

    public function setUp(): void
    {
        $this->typeMapper = new StaticTypeMapper();
        $this->typeMapper->setTypes([
            TestObject::class => new MutableObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ])
        ]);
        $this->typeMapper->setInputTypes([
            TestObject::class => new MockResolvableInputObjectType([
                'name'    => 'TestInputObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ])
        ]);
        $this->typeMapper->setNotMappedTypes([
            new ObjectType([
                'name' => 'TestNotMappedObject',
                'fields'  => [
                    'test'   => Type::string(),
                ]
            ])
        ]);
    }

    public function testStaticTypeMapper(): void
    {
        $this->assertTrue($this->typeMapper->canMapClassToType(TestObject::class));
        $this->assertFalse($this->typeMapper->canMapClassToType(\Exception::class));
        $this->assertTrue($this->typeMapper->canMapClassToInputType(TestObject::class));
        $this->assertFalse($this->typeMapper->canMapClassToInputType(\Exception::class));
        $this->assertInstanceOf(ObjectType::class, $this->typeMapper->mapClassToType(TestObject::class, null));
        $this->assertInstanceOf(InputObjectType::class, $this->typeMapper->mapClassToInputType(TestObject::class));
        $this->assertSame([TestObject::class], $this->typeMapper->getSupportedClasses());
        $this->assertSame('TestObject', $this->typeMapper->mapNameToType('TestObject')->name);
        $this->assertSame('TestInputObject', $this->typeMapper->mapNameToType('TestInputObject')->name);
        $this->assertSame('TestNotMappedObject', $this->typeMapper->mapNameToType('TestNotMappedObject')->name);
        $this->assertTrue($this->typeMapper->canMapNameToType('TestObject'));
        $this->assertTrue($this->typeMapper->canMapNameToType('TestInputObject'));
        $this->assertTrue($this->typeMapper->canMapNameToType('TestNotMappedObject'));
        $this->assertFalse($this->typeMapper->canMapNameToType('NotExists'));
        $this->assertFalse($this->typeMapper->canDecorateInputTypeForName('TestInputObject', new MockResolvableInputObjectType(['name'=>'foo'])));
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToType(\Exception::class, null);
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToInputType(\Exception::class);
    }

    public function testException3(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapNameToType('notExists');
    }

    public function testException4(): void
    {
        $type = new MutableObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->typeMapper->extendTypeForClass('foo', $type);
    }

    public function testException5(): void
    {
        $type = new MutableObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->typeMapper->extendTypeForName('foo', $type);
    }

    public function testException6(): void
    {
        $type = new MockResolvableInputObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->typeMapper->decorateInputTypeForName('foo', $type);
    }

    public function testUnsupportedSubtypes(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToType(TestObject::class, new StringType());
    }
}
