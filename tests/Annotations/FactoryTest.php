<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class FactoryTest extends TestCase
{

    public function testExceptionInConstruct(): void
    {
        $this->expectException(GraphQLRuntimeException::class);
        new Factory(['default'=>false]);
    }
}
