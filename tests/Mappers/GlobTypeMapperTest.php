<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\FooType;
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

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NullCache());

        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapClassToType(TestObject::class));
        $this->assertSame([TestObject::class], $mapper->getSupportedClasses());

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToType(\stdClass::class);
    }

    public function testGlobTypeMapperException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType();
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\DuplicateTypes', $typeGenerator, $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperClassNotFoundException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType();
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\BadClassType', $typeGenerator, $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NullCache());

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'Foobar' defined in @Type annotation of class 'TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\BadClassType\\TestType'");
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperInputType()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType();
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $container, new \TheCodingMachine\GraphQL\Controllers\AnnotationReader(new AnnotationReader()), new NullCache());

        $this->assertFalse($mapper->canMapClassToInputType(TestObject::class));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToInputType(TestType::class);
    }
}
