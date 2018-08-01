<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use Youshido\GraphQL\Type\InputObject\InputObjectType;
use Youshido\GraphQL\Type\Object\ObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;

class StaticTypeMapperTest extends TestCase
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
                    'test'   => new StringType(),
                ],
            ])
        ]);
        $this->typeMapper->setInputTypes([
            TestObject::class => new InputObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => new StringType(),
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
        $this->assertInstanceOf(ObjectType::class, $this->typeMapper->mapClassToType(TestObject::class));
        $this->assertInstanceOf(InputObjectType::class, $this->typeMapper->mapClassToInputType(TestObject::class));
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToType(\Exception::class);
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->typeMapper->mapClassToInputType(\Exception::class);
    }
}
