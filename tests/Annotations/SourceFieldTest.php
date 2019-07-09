<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class SourceFieldTest extends TestCase
{

    public function testExceptionInConstruct(): void
    {
        $this->expectException(BadMethodCallException::class);
        new SourceField([]);
    }
}
