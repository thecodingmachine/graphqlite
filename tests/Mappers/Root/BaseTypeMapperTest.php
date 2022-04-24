<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Resource_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class BaseTypeMapperTest extends AbstractQueryProviderTest
{
    public function testOutputNullableValueIterator(): void
    {
        $typeMapper = $this->getRootTypeMapper();

        $result = $typeMapper->toGraphQLOutputType($this->resolveType('ArrayObject<?int>'), null, new ReflectionMethod(__CLASS__, 'testOutputNullableValueIterator'), new DocBlock());
        $this->assertInstanceOf(NonNull::class, $result);
        $this->assertInstanceOf(ListOfType::class, $result->getWrappedType());
        $this->assertInstanceOf(IntType::class, $result->getWrappedType()->getWrappedType());
    }

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
        $this->expectExceptionMessage("type-hinting against DateTime is not allowed. Please use the DateTimeImmutable type instead.");
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
}
