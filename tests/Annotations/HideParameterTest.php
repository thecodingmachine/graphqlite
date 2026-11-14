<?php

namespace TheCodingMachine\GraphQLite\Tests\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;

class HideParameterTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        (new HideParameter([]))->getTarget();
    }
}
