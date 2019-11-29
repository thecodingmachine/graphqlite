<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\NamingStrategy;

class StaticClassListTypeMapperTest extends AbstractQueryProviderTest
{
    public function testClassListException(): void
    {
        $container = new EmptyContainer();

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new StaticClassListTypeMapper(['NotExistsClass'], $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new \TheCodingMachine\GraphQLite\AnnotationReader(new AnnotationReader()), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('Could not find class "NotExistsClass"');

        $mapper->getSupportedClasses();
    }
}
