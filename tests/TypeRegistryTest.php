<?php

namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;

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
}
