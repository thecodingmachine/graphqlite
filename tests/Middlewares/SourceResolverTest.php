<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class SourceResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new SourceResolver('test');
        $this->expectException(GraphQLRuntimeException::class);
        $sourceResolver();
    }

    public function testToString()
    {
        $sourceResolver = new SourceResolver('test');
        $sourceResolver->setObject(new stdClass());
        $this->assertSame('stdClass::test()', $sourceResolver->toString());
    }
}
