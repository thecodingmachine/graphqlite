<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Mouf\Picotainer\Picotainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;

class GlobTypeMapperTest extends AbstractQueryProviderTest
{
    public function testGlobTypeMapper()
    {
        $container = new Picotainer([
            TestType::class => function() {
                return new TestType($this->getRegistry());
            }
        ]);

        $mapper = new GlobTypeMapper('TheCodingMachine\GraphQL\Controllers\Fixtures', $container, new AnnotationReader(), new NullCache());

        $this->assertTrue($mapper->canMapClassToType(TestType::class));
        $this->assertInstanceOf(TestType::class, $mapper->mapClassToType(TestType::class));
    }
}
