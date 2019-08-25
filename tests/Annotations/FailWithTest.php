<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class FailWithTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @FailWith annotation must be passed a defaultValue. For instance: "@FailWith(null)"');
        new FailWith([]);
    }
}
