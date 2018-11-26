<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassA;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassB;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassC;

class RecursiveTypeMapperTest extends TestCase
{

    public function testMapClassToType()
    {
        $objectType = new ObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setTypes([
            ClassB::class => $objectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper);

        $this->assertFalse($typeMapper->canMapClassToType(ClassC::class));
        $this->assertTrue($recursiveTypeMapper->canMapClassToType(ClassC::class));
        $this->assertSame($objectType, $recursiveTypeMapper->mapClassToType(ClassC::class));

        $this->assertFalse($recursiveTypeMapper->canMapClassToType(ClassA::class));
        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToType(ClassA::class);
    }

    public function testMapClassToInputType()
    {
        $inputObjectType = new InputObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setInputTypes([
            ClassB::class => $inputObjectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper);

        $this->assertFalse($recursiveTypeMapper->canMapClassToInputType(ClassC::class));

        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToInputType(ClassC::class);
    }
}
