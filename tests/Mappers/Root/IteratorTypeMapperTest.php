<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use phpDocumentor\Reflection\DocBlock;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class IteratorTypeMapperTest extends AbstractQueryProviderTest
{
    public function testInputIterator(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        // A type like ArrayObject|int[] CAN be mapped to an output type, but NOT to an input type.
        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('cannot map class "ArrayObject" to a known GraphQL input type. Check your TypeMapper configuration.');
        $typeMapper->toGraphQLInputType($this->resolveType('ArrayObject|int[]'), null, 'foo', new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }

    public function testOutputNullableValueIterator(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $result = $typeMapper->toGraphQLOutputType($this->resolveType('ArrayObject|array<?int>'), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
        $this->assertInstanceOf(NonNull::class, $result);
        $this->assertInstanceOf(ListOfType::class, $result->getWrappedType());
        $this->assertInstanceOf(IntType::class, $result->getWrappedType()->getWrappedType());
    }

    public function testMixIterableWithNonArrayType(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('"\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $result = $typeMapper->toGraphQLOutputType($this->resolveType('ArrayObject|'.TestObject::class), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }

    public function testIterableWithTwoArrays(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('"\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $result = $typeMapper->toGraphQLOutputType($this->resolveType('ArrayObject|array<int>|array<string>'), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }
}
