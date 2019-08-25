<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class AutowireTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Autowire annotation must be passed a target. For instance: "@Autowire(for="$myService")"');
        new Autowire([]);
    }
}
