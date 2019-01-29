<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtendTypeTest extends TestCase
{

    public function testException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('In annotation @ExtendType, missing compulsory parameter "class".');
        new ExtendType([]);
    }
}
