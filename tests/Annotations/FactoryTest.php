<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLException;

class FactoryTest extends TestCase
{

    public function testExceptionInConstruct(): void
    {
        $this->expectException(GraphQLException::class);
        new Factory(['default'=>false]);
    }
}
