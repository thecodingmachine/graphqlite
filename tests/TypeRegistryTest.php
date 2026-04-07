<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class TypeRegistryTest extends TestCase
{
    public function testRegisterTypeException(): void
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => static function () {
                return [];
            },
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);

        $this->expectException(GraphQLRuntimeException::class);
        $registry->registerType($type);
    }

    public function testGetType(): void
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => static function () {
                return [];
            },
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);

        $this->assertSame($type, $registry->getType('Foo'));

        $this->expectException(GraphQLRuntimeException::class);
        $registry->getType('Bar');
    }

    public function testHasType(): void
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => static function () {
                return [];
            },
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);

        $this->assertTrue($registry->hasType('Foo'));
        $this->assertFalse($registry->hasType('Bar'));
    }

    public function testGetMutableObjectType(): void
    {
        $type = new MutableObjectType([
            'name' => 'Foo',
            'fields' => static function () {
                return [];
            },
        ]);
        $type2 = new ObjectType([
            'name' => 'FooBar',
            'fields' => static function () {
                return [];
            },
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);
        $registry->registerType($type2);

        $this->assertSame($type, $registry->getMutableObjectType('Foo'));

        $this->expectException(GraphQLRuntimeException::class);
        $this->assertSame($type, $registry->getMutableObjectType('FooBar'));
    }

    public function testGetMutableInterface(): void
    {
        $type = new MutableObjectType([
            'name' => 'Foo',
            'fields' => static function () {
                return [];
            },
        ]);
        $type2 = new ObjectType([
            'name' => 'FooBar',
            'fields' => static function () {
                return [];
            },
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);
        $registry->registerType($type2);

        $this->assertSame($type, $registry->getMutableInterface('Foo'));

        $this->expectException(GraphQLRuntimeException::class);
        $this->assertSame($type, $registry->getMutableInterface('FooBar'));
    }

    public function testFinalizeTypesFreezesMutableTypesOnly(): void
    {
        $mutableObjectType = $this->getMockBuilder(MutableObjectType::class)
            ->setConstructorArgs([
                [
                    'name' => 'Foo',
                    'fields' => static function () {
                        return [];
                    },
                ],
            ])
            ->onlyMethods(['freeze'])
            ->getMock();

        $mutableObjectType->expects($this->once())
            ->method('freeze');

        $mutableInterfaceType = $this->getMockBuilder(MutableInterfaceType::class)
            ->setConstructorArgs([
                [
                    'name' => 'Bar',
                    'fields' => static fn () => [],
                ],
            ])
            ->onlyMethods(['freeze'])
            ->getMock();

        $mutableInterfaceType->expects($this->once())
            ->method('freeze');

        $regularObjectType = new ObjectType([
            'name' => 'Baz',
            'fields' => static fn () => [],
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($mutableObjectType);
        $registry->registerType($mutableInterfaceType);
        $registry->registerType($regularObjectType);

        $registry->finalizeTypes();
    }
}
