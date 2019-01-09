<?php

namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\StringType;
use Porpaginas\Arrays\ArrayResult;
use RuntimeException;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;

class PorpaginasTypeMapperTest extends AbstractQueryProviderTest
{
    public function testException()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapClassToType("\stdClass", null, $this->getTypeMapper());
    }

    public function testException2()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->expectException(RuntimeException::class);
        $mapper->mapClassToType(ArrayResult::class, new ListOfType(new StringType()), $this->getTypeMapper());
    }

    public function testException3()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapNameToType('foo', $this->getTypeMapper());
    }

    public function testException4()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapNameToType('PorpaginasResult_TestObjectInput', $this->getTypeMapper());
    }

    public function testException5()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapClassToInputType('foo', $this->getTypeMapper());
    }

    public function testCanMapClassToInputType()
    {
        $mapper = new PorpaginasTypeMapper();

        $this->assertFalse($mapper->canMapClassToInputType('foo'));
    }
}
