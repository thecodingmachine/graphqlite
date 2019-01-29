<?php

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class IDTest extends TestCase
{
    public function testConstructException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ID(new stdClass());
    }

    public function testVal()
    {
        $id = new ID(42);
        $this->assertSame(42, $id->val());
        $this->assertSame('42', (string) $id);
    }
}
