<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\GraphQLException;

class BaseTypeMapperTest extends AbstractQueryProviderTest
{

    public function testNullableToGraphQLInputType()
    {
        $baseTypeMapper = new BaseTypeMapper($this->getTypeMapper());

        $mappedType = $baseTypeMapper->toGraphQLInputType(new Nullable(new Object_(new Fqsen('\\Exception'))), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
        $this->assertNull($mappedType);
    }

    public function testToGraphQLOutputTypeException()
    {
        $baseTypeMapper = new BaseTypeMapper($this->getTypeMapper());

        $this->expectException(GraphQLException::class);
        $this->expectExceptionMessage('Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
        $baseTypeMapper->toGraphQLInputType(new Object_(new Fqsen('\\DateTime')), null, 'foo', new ReflectionMethod(BaseTypeMapper::class, '__construct'), new DocBlock());
    }
}
