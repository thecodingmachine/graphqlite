<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\NullCache;
use Test;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\FilterDecorator;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactoryNoType;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\TypeGenerator;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

class GlobTypeMapperTest extends AbstractQueryProviderTest
{
    public function testGlobTypeMapper()
    {
        $container = new Picotainer([
            FooType::class => function () {
                return new FooType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertSame([TestObject::class], $mapper->getSupportedClasses());
        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapClassToType(TestObject::class, null, $this->getTypeMapper()));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapNameToType('Foo', $this->getTypeMapper()));
        $this->assertTrue($mapper->canMapNameToType('Foo'));
        $this->assertFalse($mapper->canMapNameToType('NotExists'));

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);
        $this->assertTrue($anotherMapperSameCache->canMapClassToType(TestObject::class));
        $this->assertTrue($anotherMapperSameCache->canMapNameToType('Foo'));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToType(\stdClass::class, null, $this->getTypeMapper());
    }

    public function testGlobTypeMapperDuplicateTypesException()
    {
        $container = new Picotainer([
            TestType::class => function () {
                return new TestType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\DuplicateTypes', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), new NullCache());

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

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $this->expectExceptionMessage('The class \'TheCodingMachine\GraphQLite\Fixtures\TestObject\' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory::myFactory\' and \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory2::myFactory\'');
        $mapper->canMapClassToInputType(TestObject::class);
    }

    public function testGlobTypeMapperClassNotFoundException()
    {
        $container = new Picotainer([
            TestType::class => function () {
                return new TestType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\BadClassType', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), new NullCache());

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'Foobar' defined in @Type annotation of class 'TheCodingMachine\\GraphQLite\\Fixtures\\BadClassType\\TestType'");
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperNameNotFoundException()
    {
        $container = new Picotainer([
            FooType::class => function () {
                return new FooType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), new NullCache());

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapNameToType('NotExists', $this->getTypeMapper());
    }

    public function testGlobTypeMapperInputType()
    {
        $container = new Picotainer([
            FooType::class => function () {
                return new FooType();
            },
            TestFactory::class => function () {
                return new TestFactory();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertTrue($mapper->canMapClassToInputType(TestObject::class));

        $inputType = $mapper->mapClassToInputType(TestObject::class, $this->getTypeMapper());

        $this->assertSame('TestObjectInput', $inputType->name);

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertTrue($anotherMapperSameCache->canMapClassToInputType(TestObject::class));
        $this->assertSame('TestObjectInput', $anotherMapperSameCache->mapClassToInputType(TestObject::class, $this->getTypeMapper())->name);


        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToInputType(TestType::class, $this->getTypeMapper());
    }

    public function testGlobTypeMapperExtend()
    {
        $container = new Picotainer([
            FooType::class => function () {
                return new FooType();
            },
            FooExtendType::class => function () {
                return new FooExtendType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $type = $mapper->mapClassToType(TestObject::class, null, $this->getTypeMapper());

        $this->assertTrue($mapper->canExtendTypeForClass(TestObject::class, $type, $this->getTypeMapper()));
        $mapper->extendTypeForClass(TestObject::class, $type, $this->getTypeMapper());
        $mapper->extendTypeForName('TestObject', $type, $this->getTypeMapper());
        $this->assertTrue($mapper->canExtendTypeForName('TestObject', $type, $this->getTypeMapper()));
        $this->assertFalse($mapper->canExtendTypeForName('NotExists', $type, $this->getTypeMapper()));

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);
        $this->assertTrue($anotherMapperSameCache->canExtendTypeForClass(TestObject::class, $type, $this->getTypeMapper()));
        $this->assertTrue($anotherMapperSameCache->canExtendTypeForName('TestObject', $type, $this->getTypeMapper()));

        $this->expectException(CannotMapTypeException::class);
        $mapper->extendTypeForClass(\stdClass::class, $type, $this->getTypeMapper());
    }

    public function testEmptyGlobTypeMapper()
    {
        $container = new Picotainer([]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers', $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertSame([], $mapper->getSupportedClasses());
    }

    public function testGlobTypeMapperDecorate()
    {
        $container = new Picotainer([
            FilterDecorator::class => function () {
                return new FilterDecorator();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new ArrayCache();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Integration\Types', $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $inputType = new MockResolvableInputObjectType(['name'=>'FilterInput']);

        $mapper->decorateInputTypeForName('FilterInput', $inputType);

        $this->assertCount(3, $inputType->getDecorators());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('cannot decorate GraphQL input type "FilterInput" with type "NotExists". Check your TypeMapper configuration.');
        $mapper->decorateInputTypeForName('NotExists', $inputType);
    }

    public function testInvalidName()
    {
        $container = new Picotainer([
            FooType::class => function () {
                return new FooType();
            }
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQLite\Fixtures\Types', $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), new ArrayCache());

        $this->assertFalse($mapper->canExtendTypeForName('{}()/\\@:', new MutableObjectType(['name' => 'foo'])));
        $this->assertFalse($mapper->canDecorateInputTypeForName('{}()/\\@:', new MockResolvableInputObjectType(['name' => 'foo'])));
        $this->assertFalse($mapper->canMapNameToType('{}()/\\@:'));
    }
}
