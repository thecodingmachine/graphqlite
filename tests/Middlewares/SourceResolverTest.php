<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class SourceResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new SourceMethodResolver(stdClass::class, 'test');
        $this->expectException(GraphQLRuntimeException::class);
        $sourceResolver(null);
    }

    public function testToString()
    {
        $sourceResolver = new SourceMethodResolver(stdClass::class, 'test');

        $this->assertSame('stdClass::test()', $sourceResolver->toString());
    }
}
