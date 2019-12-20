<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionMethod;
use RuntimeException;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class CompoundTypeMapperTest extends AbstractQueryProviderTest
{
    public function testException1()
    {
        $compoundTypeMapper = new CompoundTypeMapper(
            new FinalRootTypeMapper($this->getTypeMapper()),
            new FinalRootTypeMapper($this->getTypeMapper()),
            $this->getTypeRegistry(),
            $this->getTypeMapper()
        );

        $this->expectException(InvalidArgumentException::class);
        $compoundTypeMapper->toGraphQLOutputType(new Compound([]), null, new ReflectionMethod(__CLASS__, 'testException1'), new DocBlock());
    }

    public function testException2()
    {
        $compoundTypeMapper = new CompoundTypeMapper(
            new FinalRootTypeMapper($this->getTypeMapper()),
            new FinalRootTypeMapper($this->getTypeMapper()),
            $this->getTypeRegistry(),
            $this->getTypeMapper()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Iterable compound type cannot be alone in the compound.');
        $compoundTypeMapper->toGraphQLOutputType(new Compound([new Iterable_()]), null, new ReflectionMethod(__CLASS__, 'testException1'), new DocBlock());
    }

    public function testException3()
    {
        $compoundTypeMapper = $this->getRootTypeMapper();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('the value must be iterable, but its computed GraphQL type (String!) is not a list.');
        $compoundTypeMapper->toGraphQLOutputType(new Compound([new Iterable_(), new String_()]), null, new ReflectionMethod(__CLASS__, 'testException1'), new DocBlock());
    }
}
