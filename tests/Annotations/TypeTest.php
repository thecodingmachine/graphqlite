<?php

namespace TheCodingMachine\GraphQL\Controllers\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{

    public function testException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('In annotation @Type, missing compulsory parameter "class".');
        new Type([]);
    }
}
