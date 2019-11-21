<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\NonNull;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\TypeResolver as PhpDocumentorTypeResolver;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\TypeMappingRuntimeException;
use phpDocumentor\Reflection\Type;

class NullableTypeMapperAdapterTest extends AbstractQueryProviderTest
{
    public function testMultipleCompound(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $result = $compoundTypeMapper->toGraphQLOutputType($this->resolveType(TestObject::class.'|'.TestObject2::class.'|null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
        $this->assertNotInstanceOf(NonNull::class, $result);
    }

    public function testOnlyNull(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(TypeMappingRuntimeException::class);
        $this->expectExceptionMessage('Don\'t know how to handle type null');
        $compoundTypeMapper->toGraphQLOutputType($this->resolveType('null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }

    public function testOnlyNull2(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(TypeMappingRuntimeException::class);
        $this->expectExceptionMessage('Don\'t know how to handle type null');
        $compoundTypeMapper->toGraphQLInputType($this->resolveType('null'), null, 'foo', new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }
}
