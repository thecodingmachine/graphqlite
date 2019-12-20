<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\NonNull;
use phpDocumentor\Reflection\DocBlock;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

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

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('type-hinting against null only in the PHPDoc is not allowed.');
        $compoundTypeMapper->toGraphQLOutputType($this->resolveType('null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }

    public function testOnlyNull2(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('type-hinting against null only in the PHPDoc is not allowed.');
        $compoundTypeMapper->toGraphQLInputType($this->resolveType('null'), null, 'foo', new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }
}
