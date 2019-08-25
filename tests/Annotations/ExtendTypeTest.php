<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtendTypeTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('In annotation @ExtendType, missing one of the compulsory parameter "class" or "name".');
        new ExtendType([]);
    }
}
