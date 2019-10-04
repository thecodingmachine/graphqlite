<?php

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;

class GraphQLAggregateExceptionTest extends TestCase
{

    public function testAggregateException()
    {
        $error = new Error('foo');
        $exceptions = new GraphQLAggregateException([$error]);
        $exceptions->add($error);

        $this->assertSame([$error, $error], $exceptions->getExceptions());
        $this->assertTrue($exceptions->hasExceptions());
    }
}
