<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Null_;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\TypeMappingRuntimeException;

class NullableTypeMapperAdapterTest extends AbstractQueryProviderTest
{
    public function testNullOnlyForOutputType(): void
    {
        $null = new Null_();

        $nullableTypeMapperAdapter = $this->getRootTypeMapper();
        $method = new ReflectionMethod(__CLASS__, 'testNullOnlyForOutputType');

        $this->expectException(TypeMappingRuntimeException::class);
        $this->expectExceptionMessage("Don't know how to handle type null");
        $nullableTypeMapperAdapter->toGraphQLOutputType($null, null, $method, new DocBlock());
    }

    public function testNullOnlyForInputType(): void
    {
        $null = new Null_();

        $nullableTypeMapperAdapter = $this->getRootTypeMapper();
        $method = new ReflectionMethod(__CLASS__, 'testNullOnlyForOutputType');

        $this->expectException(TypeMappingRuntimeException::class);
        $this->expectExceptionMessage("Don't know how to handle type null");
        $nullableTypeMapperAdapter->toGraphQLInputType($null, null, 'foo', $method, new DocBlock());
    }
}
