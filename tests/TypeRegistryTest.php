<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class TypeRegistryTest extends TestCase
{

    public function testRegisterTypeException()
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => function() {return [];}
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);

        $this->expectException(GraphQLException::class);
        $registry->registerType($type);
    }

    public function testGetType()
    {
        $type = new ObjectType([
            'name' => 'Foo',
            'fields' => function() {return [];}
        ]);

        $registry = new TypeRegistry();
        $registry->registerType($type);

        $this->assertSame($type, $registry->getType('Foo'));

        $this->expectException(GraphQLException::class);
        $registry->getType('Bar');
    }

    public function testHasType()
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

    public function testGetMutableObjectType()
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

        $this->expectException(GraphQLException::class);
        $this->assertSame($type, $registry->getMutableObjectType('FooBar'));
    }

}
