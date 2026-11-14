<?php

namespace TheCodingMachine\GraphQLite\Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use stdClass;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestType;
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

        $this->assertSame('TheCodingMachine\GraphQLite\Tests\Fixtures\TestType::customField()', $sourceResolver->toString());
    }
}
