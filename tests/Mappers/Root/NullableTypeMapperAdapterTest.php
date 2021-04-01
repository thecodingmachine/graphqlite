<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
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

    public function testNonNullableReturnedByWrappedMapper(): void
    {
        $typeMapper = new NullableTypeMapperAdapter();

        $typeMapper->setNext(new class implements RootTypeMapperInterface {

            public function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType
            {
                return new NonNull(new StringType());
            }

            public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType
            {
                throw new \RuntimeException('Not implemented');
            }

            public function mapNameToType(string $typeName): NamedType
            {
                throw new \RuntimeException('Not implemented');
            }
        });


        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('a type mapper returned a GraphQL\\Type\\Definition\\NonNull instance.');
        $typeMapper->toGraphQLOutputType($this->resolveType(TestObject::class.'|'.TestObject2::class.'|null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }
}
