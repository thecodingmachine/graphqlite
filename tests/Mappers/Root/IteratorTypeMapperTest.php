<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use phpDocumentor\Reflection\DocBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class IteratorTypeMapperTest extends AbstractQueryProvider
{
    public function testInputIterator(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        // A type like ArrayObject|int[] CAN be mapped to an output type, but NOT to an input type.
        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('cannot map class "ArrayObject" to a known GraphQL input type. Are you missing a @Factory annotation? If you have a @Factory annotation, is it in a namespace analyzed by GraphQLite?');
        $typeMapper->toGraphQLInputType(self::resolveType('ArrayObject|int[]'), null, 'foo', new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }

    public function testOutputNullableValueIterator(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $result = $typeMapper->toGraphQLOutputType(self::resolveType('ArrayObject|array<int|null>'), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
        $this->assertInstanceOf(NonNull::class, $result);
        $this->assertInstanceOf(ListOfType::class, $result->getWrappedType());
        $this->assertInstanceOf(IntType::class, $result->getWrappedType()->getWrappedType());
    }

    public function testMixIterableWithNonArrayType(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('"\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $result = $typeMapper->toGraphQLOutputType(self::resolveType('ArrayObject|'.TestObject::class), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }

    public function testIterableWithTwoArrays(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('"\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $result = $typeMapper->toGraphQLOutputType(self::resolveType('ArrayObject|array<int>|array<string>'), null, new ReflectionMethod(__CLASS__, 'testInputIterator'), new DocBlock());
    }

    #[DataProvider('unmappableGenericIterablesProvider')]
    public function testUnmappableGenericIterable(string $phpdocType, string $expectedMessage): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage($expectedMessage);
        $typeMapper->toGraphQLOutputType(self::resolveType($phpdocType), null, new ReflectionMethod(__CLASS__, 'testUnmappableGenericIterable'), new DocBlock());
    }

    public static function unmappableGenericIterablesProvider(): iterable
    {
        // A generic iterable already defines its item type, so other union members are not used as its item type
        yield 'generic iterable or bool' => [
            \sprintf('\ArrayObject<int, %s>|bool', TestObject::class),
            'in GraphQL, you can only use union types between objects. These types cannot be used in union types: [TestObject!]!, Boolean!',
        ];
        yield 'generic iterable or object' => [
            \sprintf('\ArrayObject<int, %s>|%s', TestObject::class, TestObject2::class),
            'in GraphQL, you can only use union types between objects. These types cannot be used in union types: [TestObject!]!, TestObject2!',
        ];
        yield 'generic iterable or array' => [
            \sprintf('\ArrayObject<int, %s>|%s[]', TestObject::class, TestObject2::class),
            'in GraphQL, you can only use union types between objects. These types cannot be used in union types: [TestObject!]!, [TestObject2!]!',
        ];
        yield 'generic iterable with unmappable item type' => [
            \sprintf('\ArrayObject<int, %s&%s>', TestObject::class, TestObject2::class),
            \sprintf("don't know how to handle type \\%s&\\%s", TestObject::class, TestObject2::class),
        ];
    }
}
