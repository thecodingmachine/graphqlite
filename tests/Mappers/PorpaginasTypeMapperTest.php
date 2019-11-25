<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\StringType;
use Porpaginas\Arrays\ArrayResult;
use Porpaginas\Result;
use RuntimeException;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class PorpaginasTypeMapperTest extends AbstractQueryProviderTest
{
    private function getPorpaginasTypeMapper(): PorpaginasTypeMapper
    {
        return new PorpaginasTypeMapper($this->getTypeMapper());
    }
    
    public function testException(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapClassToType("\stdClass", null, $this->getTypeMapper());
    }

    public function testException2(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->expectException(RuntimeException::class);
        $mapper->mapClassToType(ArrayResult::class, new ListOfType(new StringType()), $this->getTypeMapper());
    }

    public function testException3(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapNameToType('foo', $this->getTypeMapper());
    }

    public function testException4(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapNameToType('PorpaginasResult_TestObjectInput', $this->getTypeMapper());
    }

    public function testException5(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->mapClassToInputType('foo', $this->getTypeMapper());
    }

    public function testException6(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();
        $type = new MutableObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->extendTypeForClass('foo', $type, $this->getTypeMapper());
    }

    public function testException7(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();
        $type = new MutableObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->extendTypeForName('foo', $type, $this->getTypeMapper());
    }

    public function testException8(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();
        $type = new MockResolvableInputObjectType(['name'=>'foo']);

        $this->expectException(CannotMapTypeExceptionInterface::class);
        $mapper->decorateInputTypeForName('foo', $type);
    }

    public function testException9(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();
        $type = new MockResolvableInputObjectType(['name'=>'foo']);

        $this->expectException(PorpaginasMissingParameterException::class);
        $mapper->mapClassToType(Result::class, null);
    }


    public function testCanMapClassToInputType(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $this->assertFalse($mapper->canMapClassToInputType('foo'));
    }

    public function testMapNameToType(): void
    {
        $mapper = $this->getPorpaginasTypeMapper();

        $type = $mapper->mapNameToType('PorpaginasResult_TestObject', $this->getTypeMapper());

        $this->assertSame('PorpaginasResult_TestObject', $type->name);
    }
}
