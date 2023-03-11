<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\WrappingType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Resource_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class BaseTypeMapperTest extends AbstractQueryProviderTest
{
    public function testNullableToGraphQLInputType(): void
    {
        $baseTypeMapper = new BaseTypeMapper(new FinalRootTypeMapper($this->getTypeMapper()), $this->getTypeMapper(), $this->getRootTypeMapper());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage("don't know how to handle type ?\Exception");
        $baseTypeMapper->toGraphQLInputType(new Nullable(new Object_(new Fqsen('\\Exception'))), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }

    public function testToGraphQLOutputTypeException(): void
    {
        $baseTypeMapper = new BaseTypeMapper(new FinalRootTypeMapper($this->getTypeMapper()), $this->getTypeMapper(), $this->getRootTypeMapper());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('type-hinting against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
        $baseTypeMapper->toGraphQLInputType(new Object_(new Fqsen('\\DateTime')), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }

    public function testUnmappableOutputArray(): void
    {
        $baseTypeMapper = new BaseTypeMapper(new FinalRootTypeMapper($this->getTypeMapper()), $this->getTypeMapper(), $this->getRootTypeMapper());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage("don't know how to handle type resource");
        $mappedType = $baseTypeMapper->toGraphQLOutputType(new Array_(new Resource_()), null, new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }

    public function testUnmappableInputArray(): void
    {
        $baseTypeMapper = new BaseTypeMapper(new FinalRootTypeMapper($this->getTypeMapper()), $this->getTypeMapper(), $this->getRootTypeMapper());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage("don't know how to handle type resource");
        $mappedType = $baseTypeMapper->toGraphQLInputType(new Array_(new Resource_()), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }

    /**
     * @param class-string $expectedItemType
     *
     * @dataProvider genericIterablesProvider
     */
    public function testOutputGenericIterables(string $phpdocType, string $expectedItemType, string $expectedWrappedItemType = null): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $result = $typeMapper->toGraphQLOutputType($this->resolveType($phpdocType), null, new ReflectionMethod(__CLASS__, 'testOutputGenericIterables'), new DocBlock());

        $this->assertInstanceOf(NonNull::class, $result);
        $this->assertInstanceOf(ListOfType::class, $result->getWrappedType());
        $itemType = $result->getWrappedType()->getWrappedType();
        $this->assertInstanceOf($expectedItemType, $itemType);
        if ($expectedWrappedItemType !== null) {
            $this->assertInstanceOf(WrappingType::class, $itemType);
            $this->assertInstanceOf($expectedWrappedItemType, $itemType->getWrappedType());
        }
    }

    public function genericIterablesProvider(): iterable
    {
        yield '\ArrayIterator with nullable int item' => ['\ArrayIterator<int|null>', IntType::class];
        yield '\ArrayIterator with int item' => ['\ArrayIterator<int>', NonNull::class, IntType::class];

        // key information cannot be presented in GQL types for now
        yield 'iterable with provided int key and test object item' => [
            sprintf('iterable<%s>', TestObject::class),
            NonNull::class,
            MutableObjectType::class,
        ];
        yield '\Iterator with provided string key and int item' => ['\Iterator<string, int>', NonNull::class, IntType::class];
        yield '\IteratorAggregate with provided int key and bool item' => ['\IteratorAggregate<int, bool>', NonNull::class, BooleanType::class];
        yield '\Traversable with provided string key and test object item' => [
            sprintf('\Traversable<string, %s>', TestObject::class),
            NonNull::class,
            MutableObjectType::class,
        ];
    }
}
