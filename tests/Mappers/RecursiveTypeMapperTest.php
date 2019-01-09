<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassA;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassB;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassC;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\Types\ClassAType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\Types\ClassBType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\NamingStrategy;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

class RecursiveTypeMapperTest extends AbstractQueryProviderTest
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

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new ArrayCache());

        $this->assertFalse($typeMapper->canMapClassToType(ClassC::class));
        $this->assertTrue($recursiveTypeMapper->canMapClassToType(ClassC::class));
        $this->assertSame($objectType, $recursiveTypeMapper->mapClassToType(ClassC::class, null));

        $this->assertFalse($recursiveTypeMapper->canMapClassToType(ClassA::class));
        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToType(ClassA::class, null);
    }

    public function testMapNameToType()
    {
        $objectType = new ObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setTypes([
            ClassB::class => $objectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new ArrayCache());

        $this->assertTrue($recursiveTypeMapper->canMapNameToType('Foobar'));
        $this->assertSame($objectType, $recursiveTypeMapper->mapNameToType('Foobar'));
    }

    public function testMapNameToType2()
    {
        $recursiveMapper = $this->getTypeMapper();

        $this->assertTrue($recursiveMapper->canMapNameToType('ClassA'));
        $this->assertTrue($recursiveMapper->canMapNameToType('ClassB'));
        $this->assertTrue($recursiveMapper->canMapNameToType('ClassAInterface'));
        $this->assertFalse($recursiveMapper->canMapNameToType('NotExists'));
        $this->assertSame('ClassA', $recursiveMapper->mapNameToType('ClassA')->name);
        $this->assertSame('ClassAInterface', $recursiveMapper->mapNameToType('ClassAInterface')->name);

        $this->expectException(CannotMapTypeException::class);
        $recursiveMapper->mapNameToType('NotExists');
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

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new ArrayCache());

        $this->assertFalse($recursiveTypeMapper->canMapClassToInputType(ClassC::class));

        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToInputType(ClassC::class);
    }

    protected function getTypeMapper()
    {
        $container = new Picotainer([
            ClassAType::class => function() {
                return new ClassAType();
            },
            ClassBType::class => function() {
                return new ClassBType();
            }
        ]);

        $namingStrategy = new NamingStrategy();

        $typeGenerator = new TypeGenerator($this->getAnnotationReader(), $this->getControllerQueryProviderFactory(), $namingStrategy);

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), $namingStrategy, new NullCache());

        return new RecursiveTypeMapper($mapper, new NamingStrategy(), new ArrayCache());
    }

    public function testMapClassToInterfaceOrType()
    {
        $recursiveMapper = $this->getTypeMapper();

        $type = $recursiveMapper->mapClassToInterfaceOrType(ClassA::class, null);
        $this->assertInstanceOf(InterfaceType::class, $type);
        $this->assertSame('ClassAInterface', $type->name);

        $classAObjectType = $recursiveMapper->mapClassToType(ClassA::class, null);
        $this->assertInstanceOf(ObjectType::class, $classAObjectType);
        $this->assertCount(1, $classAObjectType->getInterfaces());

        $type = $recursiveMapper->mapClassToInterfaceOrType(ClassC::class, null);
        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame('ClassB', $type->name);

        $interfaces = $recursiveMapper->findInterfaces(ClassC::class);
        $this->assertCount(1, $interfaces);
        $this->assertSame('ClassAInterface', $interfaces[0]->name);

        $classBObjectType = $recursiveMapper->mapClassToType(ClassC::class, null);
        $this->assertInstanceOf(ObjectType::class, $classBObjectType);
        $this->assertCount(1, $classBObjectType->getInterfaces());

        $this->expectException(CannotMapTypeException::class);
        $recursiveMapper->mapClassToInterfaceOrType('Not exists', null);
    }

    public function testGetOutputTypes()
    {
        $recursiveMapper = $this->getTypeMapper();

        $outputTypes = $recursiveMapper->getOutputTypes();
        $this->assertArrayHasKey('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Interfaces\\ClassA', $outputTypes);
        $this->assertArrayHasKey('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Interfaces\\ClassB', $outputTypes);
        $this->assertArrayNotHasKey('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Interfaces\\ClassC', $outputTypes);
    }


}
