<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;

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
            TestObject::class => new ObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ])
        ]);
        $this->typeMapper->setInputTypes([
            TestObject::class => new InputObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ])
        ]);
    }

    public function testStaticTypeMapper(): void
    {
        $this->assertTrue($this->typeMapper->canMapClassToType(TestObject::class));
        $this->assertFalse($this->typeMapper->canMapClassToType(\Exception::class));
        $this->assertTrue($this->typeMapper->canMapClassToInputType(TestObject::class));
        $this->assertFalse($this->typeMapper->canMapClassToInputType(\Exception::class));
        $this->assertInstanceOf(ObjectType::class, $this->typeMapper->mapClassToType(TestObject::class, $this->getTypeMapper()));
        $this->assertInstanceOf(InputObjectType::class, $this->typeMapper->mapClassToInputType(TestObject::class));
        $this->assertSame([TestObject::class], $this->typeMapper->getSupportedClasses());
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToType(\Exception::class, $this->getTypeMapper());
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToInputType(\Exception::class);
    }
}
