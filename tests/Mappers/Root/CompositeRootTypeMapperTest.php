<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Integer;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CompositeRootTypeMapperTest extends TestCase
{
    private function getNullTypeMapper(): RootTypeMapperInterface
    {
        return new class() implements RootTypeMapperInterface {
            public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
            {
                return null;
            }

            public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
            {
                return null;
            }

            public function mapNameToType(string $typeName): ?NamedType
            {
                return null;
            }
        };
    }

    public function testToGraphQLInputType(): void
    {
        $typeMapper = new CompositeRootTypeMapper([$this->getNullTypeMapper()]);
        $this->assertNull($typeMapper->toGraphQLOutputType(new Integer(), null, new ReflectionMethod(CompositeRootTypeMapper::class, '__construct'), new DocBlock()));
    }

    public function testToGraphQLOutputType(): void
    {
        $typeMapper = new CompositeRootTypeMapper([$this->getNullTypeMapper()]);
        $this->assertNull($typeMapper->toGraphQLInputType(new Integer(), null, 'foo', new ReflectionMethod(CompositeRootTypeMapper::class, '__construct'), new DocBlock()));
    }
}
