<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\TypeMappingException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class CompositeTypeMapperTest extends AbstractQueryProviderTest
{
    /**
     * @var CompositeTypeMapper
     */
    protected $composite;

    public function setUp()
    {
        $typeMapper1 = new class() implements TypeMapperInterface {
            public function mapClassToType(string $className, ?OutputType $subType, RecursiveTypeMapperInterface $recursiveTypeMapper): MutableObjectType
            {
                if ($className === TestObject::class) {
                    return new MutableObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => Type::string(),
                        ],
                    ]);
                } else {
                    throw TypeMappingException::createFromType(TestObject::class);
                }
            }

            public function mapClassToInputType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
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

            /**
             * Returns the list of classes that have matching input GraphQL types.
             *
             * @return string[]
             */
            public function getSupportedClasses(): array
            {
                return [TestObject::class];
            }

            /**
             * Returns a GraphQL type by name (can be either an input or output type)
             *
             * @param string $typeName The name of the GraphQL type
             * @param RecursiveTypeMapperInterface $recursiveTypeMapper
             * @return Type&(InputType|OutputType)
             */
            public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
            {
                switch ($typeName) {
                    case 'TestObject':
                        return new MutableObjectType([
                            'name'    => 'TestObject',
                            'fields'  => [
                                'test'   => Type::string(),
                            ],
                        ]);
                    default:
                        throw CannotMapTypeException::createForName($typeName);
                }
            }

            /**
             * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
             *
             * @param string $typeName The name of the GraphQL type
             * @return bool
             */
            public function canMapNameToType(string $typeName): bool
            {
                return $typeName === 'TestObject';
            }

            public function canExtendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
            {
                return false;
            }

            public function extendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
            {
                throw CannotMapTypeException::createForExtendType($className, $type);
            }

            public function canExtendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
            {
                return true;
            }

            public function extendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
            {
                $type->addFields(function() {
                    return [
                        'test2' => Type::int()
                    ];
                });
                //throw CannotMapTypeException::createForExtendName($typeName, $type);
            }
        };

        $this->composite = new CompositeTypeMapper([$typeMapper1]);
    }


    public function testComposite(): void
    {
        $this->assertTrue($this->composite->canMapClassToType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToType(\Exception::class));
        $this->assertTrue($this->composite->canMapClassToInputType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToInputType(\Exception::class));
        $this->assertInstanceOf(ObjectType::class, $this->composite->mapClassToType(TestObject::class, null, $this->getTypeMapper()));
        $this->assertInstanceOf(InputObjectType::class, $this->composite->mapClassToInputType(TestObject::class, $this->getTypeMapper()));
        $this->assertSame([TestObject::class], $this->composite->getSupportedClasses());
        $this->assertInstanceOf(ObjectType::class, $this->composite->mapNameToType('TestObject', $this->getTypeMapper()));
        $this->assertTrue($this->composite->canMapNameToType('TestObject'));
        $this->assertFalse($this->composite->canMapNameToType('NotExists'));


        $type = new MutableObjectType([
            'name'    => 'TestObject',
            'fields'  => [
                'test'   => Type::string(),
            ],
        ]);

        $this->assertFalse($this->composite->canExtendTypeForClass('foo', $type, $this->getTypeMapper()));
        $this->assertTrue($this->composite->canExtendTypeForName('foo', $type, $this->getTypeMapper()));


        $this->composite->extendTypeForName('foo', $type, $this->getTypeMapper());

        $type->freeze();
        $this->assertCount(2, $type->getFields());
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToType(\Exception::class, null, $this->getTypeMapper());
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToInputType(\Exception::class, $this->getTypeMapper());
    }

    public function testException3(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapNameToType('NotExists', $this->getTypeMapper());
    }
}
