<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\NullCache;
use Test;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\FooType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQL\Controllers\NamingStrategy;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;
use GraphQL\Type\Definition\ObjectType;

class GlobTypeMapperTest extends AbstractQueryProviderTest
{
    public function testGlobTypeMapper()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $inputTypeGenerator, $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $cache);

        $this->assertSame([TestObject::class], $mapper->getSupportedClasses());
        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapClassToType(TestObject::class, $this->getTypeMapper()));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapNameToType('Foo', $this->getTypeMapper()));
        $this->assertTrue($mapper->canMapNameToType('Foo'));
        $this->assertFalse($mapper->canMapNameToType('NotExists'));

        $anotherMapperSameCache = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $cache);
        $this->assertTrue($anotherMapperSameCache->canMapClassToType(TestObject::class));
        $this->assertTrue($anotherMapperSameCache->canMapNameToType('Foo'));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToType(\stdClass::class, $this->getTypeMapper());
    }

    public function testGlobTypeMapperDuplicateTypesException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateTypes', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperDuplicateInputTypesException()
    {
        $container = new Picotainer([
            /*TestType::class => function() {
                return new TestType();
            }*/
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateInputTypes', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $this->expectExceptionMessage('The class \'TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject\' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: \'TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateInputTypes\TestFactory::myFactory\' and \'TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateInputTypes\TestFactory2::myFactory\'');
        $mapper->canMapClassToInputType(TestObject::class);
    }

    public function testGlobTypeMapperClassNotFoundException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\BadClassType', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), new NullCache());

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'Foobar' defined in @Type annotation of class 'TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\BadClassType\\TestType'");
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperNameNotFoundException()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), new NullCache());

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapNameToType('NotExists', $this->getTypeMapper());
    }

    public function testGlobTypeMapperInputType()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType();
            },
            TestFactory::class => function() {
                return new TestFactory();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NamingStrategy(), new NullCache());

        $this->assertTrue($mapper->canMapClassToInputType(TestObject::class));

        $inputType = $mapper->mapClassToInputType(TestObject::class, $this->getTypeMapper());

        $this->assertSame('TestObjectInput', $inputType->name);

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToInputType(TestType::class, $this->getTypeMapper());
    }
}
