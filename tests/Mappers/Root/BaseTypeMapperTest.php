<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Resource_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class BaseTypeMapperTest extends AbstractQueryProviderTest
{

    public function testNullableToGraphQLInputType(): void
    {
        $baseTypeMapper = new BaseTypeMapper($this->getTypeMapper());

        $mappedType = $baseTypeMapper->toGraphQLInputType(new Nullable(new Object_(new Fqsen('\\Exception'))), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
        $this->assertNull($mappedType);
    }

    public function testToGraphQLOutputTypeException(): void
    {
        $baseTypeMapper = new BaseTypeMapper($this->getTypeMapper());

        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
        $baseTypeMapper->toGraphQLInputType(new Object_(new Fqsen('\\DateTime')), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }

    public function testUnmappableArray(): void
    {
        $baseTypeMapper = new BaseTypeMapper($this->getTypeMapper());

        $mappedType = $baseTypeMapper->toGraphQLOutputType(new Array_(new Resource_()), null, new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
        $this->assertNull($mappedType);

        $mappedType = $baseTypeMapper->toGraphQLInputType(new Array_(new Resource_()), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
        $this->assertNull($mappedType);
    }

}
