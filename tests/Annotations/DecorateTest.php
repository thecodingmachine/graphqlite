<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class DecorateTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Decorate annotation must be passed an input type. For instance: "@Decorate("MyInputType")"');
        new Decorate([]);
    }
}
