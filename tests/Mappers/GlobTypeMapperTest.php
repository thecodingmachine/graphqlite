<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\FooType;

class GlobTypeMapperTest extends AbstractQueryProviderTest
{
    public function testGlobTypeMapper()
    {
        $container = new Picotainer([
            FooType::class => function() {
                return new FooType($this->getRegistry());
            }
        ]);

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures\Types', $container, new AnnotationReader(), new NullCache());

        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(FooType::class, $mapper->mapClassToType(TestObject::class));
    }

    public function testGlobTypeMapperException()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType($this->getRegistry());
            }
        ]);

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures', $container, new AnnotationReader(), new NullCache());

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToType(TestType::class);
    }
}
