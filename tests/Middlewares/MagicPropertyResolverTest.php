<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class MagicPropertyResolverTest extends TestCase
{

    public function testExceptionInInvoke()
    {
        $sourceResolver = new MagicPropertyResolver('test');
        $this->expectException(GraphQLRuntimeException::class);
        $sourceResolver();
    }

    public function testToString()
    {
        $sourceResolver = new MagicPropertyResolver('test');
        $sourceResolver->setObject(new stdClass());
        $this->assertSame("stdClass::__get('test')", $sourceResolver->toString());
    }

    public function testGetObject()
    {
        $sourceResolver = new MagicPropertyResolver('test');
        $obj = new stdClass();
        $sourceResolver->setObject($obj);
        $this->assertSame($obj, $sourceResolver->getObject());
    }
}
