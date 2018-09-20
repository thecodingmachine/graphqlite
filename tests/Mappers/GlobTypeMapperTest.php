<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\FooType;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;
use Youshido\GraphQL\Type\Object\ObjectType;

class GlobTypeMapperTest extends AbstractQueryProviderTest
{
    public function testGlobTypeMapper()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType($this->getRegistry());
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $container, new AnnotationReader(), new NullCache());

        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapClassToType(TestObject::class));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToType(\stdClass::class);
    }

    public function testGlobTypeMapperException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType($this->getRegistry());
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures', $typeGenerator, $container, new AnnotationReader(), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperInputType()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType($this->getRegistry());
            }
        ]);

        $typeGenerator = new TypeGenerator($this->getRegistry());

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $typeGenerator, $container, new AnnotationReader(), new NullCache());

        $this->assertFalse($mapper->canMapClassToInputType(TestObject::class));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToInputType(TestType::class);
    }
}
