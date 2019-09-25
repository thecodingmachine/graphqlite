<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use PHPUnit\Framework\TestCase;

class SecurityFieldMiddlewareTest extends TestCase
{

    public function testGetThisFromCallable()
    {
        $callable = function() {

        };
        $object = SecurityFieldMiddleware::getThisFromCallable($callable);

        $this->assertSame($this, $object);
    }
}
