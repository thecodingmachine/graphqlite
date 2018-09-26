<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\TypeMappingException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;

class CompositeTypeMapperTest extends TestCase
{
    /**
     * @var CompositeTypeMapper
     */
    protected $composite;

    public function setUp()
    {
        $typeMapper1 = new class() implements TypeMapperInterface {
            public function mapClassToType(string $className): OutputType
            {
                if ($className === TestObject::class) {
                    return new ObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => Type::string(),
                        ],
                    ]);
                } else {
                    throw TypeMappingException::createFromType(TestObject::class);
                }
            }

            public function mapClassToInputType(string $className): InputType
            {
                if ($className === TestObject::class) {
                    return new InputObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => Type::string(),
                        ],
                    ]);
                } else {
                    throw TypeMappingException::createFromType(TestObject::class);
                }
            }

            public function canMapClassToType(string $className): bool
            {
                return $className === TestObject::class;
            }

            public function canMapClassToInputType(string $className): bool
            {
                return $className === TestObject::class;
            }
        };

        $this->composite = new CompositeTypeMapper();
        $this->composite->setTypeMappers([$typeMapper1]);
    }


    public function testComposite(): void
    {
        $this->assertTrue($this->composite->canMapClassToType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToType(\Exception::class));
        $this->assertTrue($this->composite->canMapClassToInputType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToInputType(\Exception::class));
        $this->assertInstanceOf(ObjectType::class, $this->composite->mapClassToType(TestObject::class));
        $this->assertInstanceOf(InputObjectType::class, $this->composite->mapClassToInputType(TestObject::class));
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToType(\Exception::class);
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToInputType(\Exception::class);
    }
}
