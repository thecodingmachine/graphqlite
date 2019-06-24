<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MyCLabsEnumTypeMapperTest extends TestCase
{

    public function testObjectTypeHint()
    {
        $mapper = new MyCLabsEnumTypeMapper();

        $this->assertNull($mapper->toGraphQLOutputType(new Object_(), null, new ReflectionMethod(__CLASS__, 'testObjectTypeHint'), new DocBlock()));
    }
}
