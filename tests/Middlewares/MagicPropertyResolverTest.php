<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithMagicProperty;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class MagicPropertyResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new MagicPropertyResolver(stdClass::class, 'test');
        $this->expectException(GraphQLRuntimeException::class);

        $sourceResolver(null);
    }

    public function testToString()
    {
        $sourceResolver = new MagicPropertyResolver(stdClass::class, 'test');

        $this->assertSame("stdClass::__get('test')", $sourceResolver->toString());
    }

    public function testInvoke()
    {
        $sourceResolver = new MagicPropertyResolver(TestTypeWithMagicProperty::class, 'test');

        $this->assertSame('foo', $sourceResolver(new TestTypeWithMagicProperty()));
    }
}
