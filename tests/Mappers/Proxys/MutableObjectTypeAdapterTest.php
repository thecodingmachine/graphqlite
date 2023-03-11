<?php

namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TheCodingMachine\GraphQLite\Fixtures\StaticTypeMapper\Types\TestLegacyObject;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\NoFieldsException;

class MutableObjectTypeAdapterTest extends TestCase
{
    private function getAdapter(): MutableObjectTypeAdapter
    {
        $type = new ObjectType([
            'name' => 'TestLegacyObject',
            'fields' => [
                'foo' => [
                    'type' => Type::int(),
                    'resolve' => function (TestLegacyObject $source) {
                        return $source->getFoo();
                    },
                ],
            ],
        ]);

        $adapter = new MutableObjectTypeAdapter($type);

        return $adapter;
    }

    public function testAdapter()
    {
        $type = new ObjectType([
            'name' => 'TestLegacyObject',
            'fields' => [
                'foo' => [
                    'type' => Type::int(),
                    'resolve' => function (TestLegacyObject $source) {
                        return $source->getFoo();
                    },
                ],
            ],
        ]);

        $adapter = new MutableObjectTypeAdapter($type);

        $adapter->assertValid();

        $adapter->freeze();
        $this->assertSame($type->getInterfaces(), $adapter->getInterfaces());
        $this->assertSame($type->jsonSerialize(), $adapter->jsonSerialize());
        $this->assertSame($type->getField('foo'), $adapter->getField('foo'));
        $this->assertSame($type->hasField('foo'), $adapter->hasField('foo'));
        $interfaceType = new InterfaceType(['name' => 'Foo']);
        $this->assertSame($type->implementsInterface($interfaceType), $adapter->implementsInterface($interfaceType));
    }

    public function testGetStatus(): void
    {
        $type = $this->getAdapter();

        $this->assertSame(MutableObjectType::STATUS_PENDING, $type->getStatus());
        $type->freeze();
        $this->assertSame(MutableObjectType::STATUS_FROZEN, $type->getStatus());
    }

    public function testAddFields(): void
    {
        $type = $this->getAdapter();

        $type->addFields(function () {
            return [
                'test' => Type::int(),
                'test2' => Type::string(),
            ];
        });
        $type->addFields(function () {
            return [
                'test3' => Type::int(),
            ];
        });
        $type->freeze();
        $fields = $type->getFields();
        $this->assertCount(4, $fields);
        $this->assertArrayHasKey('test', $fields);
        $this->assertSame(Type::int(), $fields['test']->getType());
    }

    public function testHasFieldError(): void
    {
        $type = $this->getAdapter();

        $this->expectException(RuntimeException::class);
        $type->hasField('test');
    }

    public function testGetFieldError(): void
    {
        $type = $this->getAdapter();

        $this->expectException(RuntimeException::class);
        $type->getField('test');
    }

    public function testGetFieldsError(): void
    {
        $type = $this->getAdapter();

        $this->expectException(RuntimeException::class);
        $type->getFields();
    }

    public function testAddFieldsError(): void
    {
        $type = $this->getAdapter();

        $type->freeze();
        $this->expectException(RuntimeException::class);
        $type->addFields(function () {
        });
    }

    public function testNoFieldsType(): void
    {
        $type = new ObjectType([
            'name' => 'TestLegacyObject',
            'fields' => [
            ],
        ]);

        $adapter = new MutableObjectTypeAdapter($type);

        $adapter->freeze();
        $this->expectException(NoFieldsException::class);
        $this->expectExceptionMessage('The GraphQL object type "TestLegacyObject" has no fields defined. Please check that some fields are defined (using the @Field annotation). If some fields are defined, please check that at least one is visible to the current user.');
        $adapter->getFields();
    }
}
