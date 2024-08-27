<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class MyCLabsEnumTypeMapperTest extends AbstractQueryProvider
{
    public function testObjectTypeHint(): void
    {
        $mapper = new MyCLabsEnumTypeMapper(new FinalRootTypeMapper($this->getTypeMapper()), $this->getAnnotationReader(), $this->getClassFinder([]), $this->getClassFinderComputedCache());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage("don't know how to handle type object");
        $mapper->toGraphQLOutputType(new Object_(), null, new ReflectionMethod(self::class, 'testObjectTypeHint'), new DocBlock());
    }
}
