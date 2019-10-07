<?php

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;

class GraphQLAggregateExceptionTest extends TestCase
{

    public function testAggregateException()
    {
        $error = new GraphQLException('foo', 12);
        $error2 = new GraphQLException('bar', 42);
        $exceptions = new GraphQLAggregateException([$error]);
        $exceptions->add($error2);

        $this->assertSame([$error, $error2], $exceptions->getExceptions());
        $this->assertSame(42, $exceptions->getCode());
        $this->assertTrue($exceptions->hasExceptions());
    }
}
