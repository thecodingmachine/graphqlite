<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class HideParameterTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        new HideParameter([]);
    }
}
