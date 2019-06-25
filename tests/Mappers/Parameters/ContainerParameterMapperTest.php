<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Annotations\Autowire;

class ContainerParameterMapperTest extends AbstractQueryProviderTest
{

    public function testMapParameter()
    {
        $mapper = new ContainerParameterMapper($this->getRegistry());

        $refMethod = new ReflectionMethod(__CLASS__, 'dummy');
        $parameter = $refMethod->getParameters()[0];

        $this->expectException(MissingAutowireTypeException::class);
        $this->expectExceptionMessage('For parameter $foo in TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterMapperTest::dummy, annotated with annotation @Autowire, you must either provide a type-hint or specify the container identifier with @Autowire(identifier="my_service")');
        $mapper->mapParameter($parameter,
            new DocBlock(), null, $this->getAnnotationReader()->getParameterAnnotations($parameter));
    }

    /**
     * @Autowire(for="foo")
     */
    private function dummy($foo) {

    }
}
