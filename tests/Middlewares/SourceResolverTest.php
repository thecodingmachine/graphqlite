<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class SourceResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new SourceMethodResolver(new ReflectionMethod(TestType::class, 'customField'));
        $this->expectException(GraphQLRuntimeException::class);
        $sourceResolver(null);
    }

    public function testToString()
    {
        $sourceResolver = new SourceMethodResolver(new ReflectionMethod(TestType::class, 'customField'));

        $this->assertSame('TheCodingMachine\GraphQLite\Fixtures\TestType::customField()', $sourceResolver->toString());
    }
}
