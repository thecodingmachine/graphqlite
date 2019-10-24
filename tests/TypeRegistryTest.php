<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class TypeRegistryTest extends TestCase
{

    public function testRegisterTypeException(): void
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => function() {return [];}
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
            'fields' => function() {return [];}
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
            'fields' => function() {return [];}
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
            'fields' => function() {return [];}
        ]);
        $type2 = new ObjectType([
            'name' => 'FooBar',
            'fields' => function() {return [];}
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
            'fields' => function() {return [];}
        ]);
        $type2 = new ObjectType([
            'name' => 'FooBar',
            'fields' => function() {return [];}
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);
        $registry->registerType($type2);

        $this->assertSame($type, $registry->getMutableInterface('Foo'));

        $this->expectException(GraphQLRuntimeException::class);
        $this->assertSame($type, $registry->getMutableInterface('FooBar'));
    }
}
