<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Generator;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Nullable;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class NullableTypeMapperAdapterTest extends AbstractQueryProvider
{
    #[DataProvider('nullableVariationsProvider')]
    public function testMultipleCompound(callable $type): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $result = $compoundTypeMapper->toGraphQLOutputType($type(), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
        $this->assertNotInstanceOf(NonNull::class, $result);
    }

    public static function nullableVariationsProvider(): Generator
    {
        yield 'php documentor generated from phpdoc' => [
            fn () => self::resolveType(TestObject::class . '|' . TestObject2::class . '|null'),
        ];

        yield 'type handler nullable wrapped native reflection union type' => [
            fn () => new Nullable(self::resolveType(TestObject::class . '|' . TestObject2::class . '|null')),
        ];
    }

    public function testOnlyNull(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('type-hinting against null only in the PHPDoc is not allowed.');
        $compoundTypeMapper->toGraphQLOutputType(self::resolveType('null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }

    public function testOnlyNull2(): void
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('type-hinting against null only in the PHPDoc is not allowed.');
        $compoundTypeMapper->toGraphQLInputType(self::resolveType('null'), null, 'foo', new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }

    public function testNonNullableReturnedByWrappedMapper(): void
    {
        $next = new class implements RootTypeMapperInterface {

            public function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
            {
                return new NonNull(new StringType());
            }

            public function toGraphQLInputType(Type $type, null|InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType&GraphQLType
            {
                throw new \RuntimeException('Not implemented');
            }

            public function mapNameToType(string $typeName): NamedType&GraphQLType
            {
                throw new \RuntimeException('Not implemented');
            }
        };

        $typeMapper = new NullableTypeMapperAdapter($next);


        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('a type mapper returned a GraphQL\\Type\\Definition\\NonNull instance.');
        $typeMapper->toGraphQLOutputType(self::resolveType(TestObject::class . '|' . TestObject2::class . '|null'), null, new ReflectionMethod(__CLASS__, 'testMultipleCompound'), new DocBlock());
    }
}
