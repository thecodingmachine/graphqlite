<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class ServiceResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new SourceResolver('test');
        $this->expectException(GraphQLRuntimeException::class);
        $sourceResolver();
    }

    public function testGetMethodName()
    {
        $sourceResolver = new SourceResolver('test');
        $this->assertSame('test', $sourceResolver->getMethodName());
    }
}
