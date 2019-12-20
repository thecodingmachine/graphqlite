<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;

class CompositeTypeMapperTest extends AbstractQueryProviderTest
{
    /**
     * @var CompositeTypeMapper
     */
    protected $composite;

    public function setUp(): void
    {
        $typeMapper1 = new class() implements TypeMapperInterface {
            public function mapClassToType(string $className, ?OutputType $subType): \TheCodingMachine\GraphQLite\Types\MutableInterface
            {
                if ($className === TestObject::class) {
                    return new MutableObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => Type::string(),
                        ],
                    ]);
                } else {
                    throw CannotMapTypeException::createForType(TestObject::class);
                }
            }

            public function mapClassToInputType(string $className): ResolvableMutableInputInterface
            {
                if ($className === TestObject::class) {
                    return new MockResolvableInputObjectType([
                        'name'    => 'TestObject',
                        'fields'  => [
                            'test'   => Type::string(),
                        ],
                    ]);
                } else {
                    throw CannotMapTypeException::createForType(TestObject::class);
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
             * @return NamedType&Type&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType)
             */
            public function mapNameToType(string $typeName): Type
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

            public function canExtendTypeForClass(string $className, MutableInterface $type): bool
            {
                return false;
            }

            public function extendTypeForClass(string $className, MutableInterface $type): void
            {
                throw CannotMapTypeException::createForExtendType($className, $type);
            }

            public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
            {
                return true;
            }

            public function extendTypeForName(string $typeName, MutableInterface $type): void
            {
                $type->addFields(function() {
                    return [
                        'test2' => Type::int()
                    ];
                });
                //throw CannotMapTypeException::createForExtendName($typeName, $type);
            }

            /**
             * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
             *
             * @param string $typeName
             * @param ResolvableMutableInputInterface $type
             * @return bool
             */
            public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
            {
                return false;
            }

            /**
             * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
             *
             * @param string $typeName
             * @param ResolvableMutableInputInterface $type
             * @throws CannotMapTypeExceptionInterface
             */
            public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
            {
                throw CannotMapTypeException::createForDecorateName($typeName, $type);
            }
        };

        $this->composite = new CompositeTypeMapper();
        $this->composite->addTypeMapper($typeMapper1);
    }


    public function testComposite(): void
    {
        $this->assertTrue($this->composite->canMapClassToType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToType(\Exception::class));
        $this->assertTrue($this->composite->canMapClassToInputType(TestObject::class));
        $this->assertFalse($this->composite->canMapClassToInputType(\Exception::class));
        $this->assertInstanceOf(ObjectType::class, $this->composite->mapClassToType(TestObject::class, null));
        $this->assertInstanceOf(InputObjectType::class, $this->composite->mapClassToInputType(TestObject::class));
        $this->assertSame([TestObject::class], $this->composite->getSupportedClasses());
        $this->assertInstanceOf(ObjectType::class, $this->composite->mapNameToType('TestObject'));
        $this->assertTrue($this->composite->canMapNameToType('TestObject'));
        $this->assertFalse($this->composite->canMapNameToType('NotExists'));
        $this->assertFalse($this->composite->canDecorateInputTypeForName('Foo', new MockResolvableInputObjectType(['name' => 'foo',
            'fields' => [
            'arg' => Type::string()
        ]]
        )));


        $type = new MutableObjectType([
            'name'    => 'TestObject',
            'fields'  => [
                'test'   => Type::string(),
            ],
        ]);

        $this->assertFalse($this->composite->canExtendTypeForClass('foo', $type));
        $this->assertTrue($this->composite->canExtendTypeForName('foo', $type));


        $this->composite->extendTypeForName('foo', $type);

        $type->freeze();
        $this->assertCount(2, $type->getFields());
    }

    public function testException1(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToType(\Exception::class, null);
    }

    public function testException2(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapClassToInputType(\Exception::class);
    }

    public function testException3(): void
    {
        $this->expectException(CannotMapTypeException::class);
        $this->composite->mapNameToType('NotExists');
    }
}
