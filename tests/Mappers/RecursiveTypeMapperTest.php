<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassA;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassB;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassC;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\Types\ClassAType;
use TheCodingMachine\GraphQLite\Fixtures\Interfaces\Types\ClassBType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

class RecursiveTypeMapperTest extends AbstractQueryProviderTest
{

    public function testMapClassToType(): void
    {
        $objectType = new MutableObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setTypes([
            ClassB::class => $objectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $this->assertFalse($typeMapper->canMapClassToType(ClassC::class));
        $this->assertTrue($recursiveTypeMapper->canMapClassToType(ClassC::class));
        $this->assertSame($objectType, $recursiveTypeMapper->mapClassToType(ClassC::class, null));

        $this->assertFalse($recursiveTypeMapper->canMapClassToType(ClassA::class));
        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToType(ClassA::class, null);
    }

    public function testMapNameToType(): void
    {
        $objectType = new MutableObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setTypes([
            ClassB::class => $objectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $this->assertTrue($recursiveTypeMapper->canMapNameToType('Foobar'));
        $this->assertSame($objectType, $recursiveTypeMapper->mapNameToType('Foobar'));
    }

    public function testMapNameToType2(): void
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


    public function testMapClassToInputType(): void
    {
        $inputObjectType = new InputObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper = new StaticTypeMapper();
        $typeMapper->setInputTypes([
            ClassB::class => $inputObjectType
        ]);

        $recursiveTypeMapper = new RecursiveTypeMapper($typeMapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $this->assertFalse($recursiveTypeMapper->canMapClassToInputType(ClassC::class));

        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapClassToInputType(ClassC::class);
    }

    protected $typeMapper;

    protected function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $container = new Picotainer([
                ClassAType::class => function () {
                    return new ClassAType();
                },
                ClassBType::class => function () {
                    return new ClassBType();
                }
            ]);

            $namingStrategy = new NamingStrategy();


            $compositeMapper = new CompositeTypeMapper();
            $this->typeMapper = new RecursiveTypeMapper($compositeMapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

            $typeGenerator = new TypeGenerator($this->getAnnotationReader(), $namingStrategy, $this->getTypeRegistry(), $this->getRegistry(), $this->typeMapper, $this->getFieldsBuilder());

            $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Interfaces\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), $namingStrategy, $this->typeMapper, new Psr16Cache(new NullAdapter()));
            $compositeMapper->addTypeMapper($mapper);
        }
        return $this->typeMapper;
    }

    public function testMapClassToInterfaceOrType(): void
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

    public function testGetOutputTypes(): void
    {
        $recursiveMapper = $this->getTypeMapper();

        $outputTypes = $recursiveMapper->getOutputTypes();
        $this->assertArrayHasKey('TheCodingMachine\\GraphQLite\\Fixtures\\Interfaces\\ClassA', $outputTypes);
        $this->assertArrayHasKey('TheCodingMachine\\GraphQLite\\Fixtures\\Interfaces\\ClassB', $outputTypes);
        $this->assertArrayNotHasKey('TheCodingMachine\\GraphQLite\\Fixtures\\Interfaces\\ClassC', $outputTypes);
    }

    public function testDuplicateDetection(): void
    {
        $objectType = new MutableObjectType([
            'name' => 'Foobar'
        ]);

        $typeMapper1 = new StaticTypeMapper();
        $typeMapper1->setTypes([
            ClassB::class => $objectType
        ]);

        $typeMapper2 = new StaticTypeMapper();
        $typeMapper2->setTypes([
            ClassA::class => $objectType
        ]);

        $compositeTypeMapper = new CompositeTypeMapper();
        $compositeTypeMapper->addTypeMapper($typeMapper1);
        $compositeTypeMapper->addTypeMapper($typeMapper2);

        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $this->expectException(DuplicateMappingException::class);
        $this->expectExceptionMessage("The type 'Foobar' is created by 2 different classes: 'TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassB' and 'TheCodingMachine\GraphQLite\Fixtures\Interfaces\ClassA'");
        $recursiveTypeMapper->getOutputTypes();
    }

    /**
     * Tests that the RecursiveTypeMapper behaves correctly if there are no types to map.
     */
    public function testMapNoTypes(): void
    {
        $recursiveTypeMapper = new RecursiveTypeMapper(new CompositeTypeMapper(), new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $this->expectException(CannotMapTypeException::class);
        $recursiveTypeMapper->mapNameToType('Foo');
    }

    public function testMapNameToTypeDecorators(): void
    {
        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Integration', $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $this->getRegistry(), new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $recursiveTypeMapper = new RecursiveTypeMapper($mapper, new NamingStrategy(), new Psr16Cache(new ArrayAdapter()), $this->getTypeRegistry());

        $type = $recursiveTypeMapper->mapNameToType('FilterInput');
        $this->assertInstanceOf(ResolvableMutableInputObjectType::class, $type);
    }
}
