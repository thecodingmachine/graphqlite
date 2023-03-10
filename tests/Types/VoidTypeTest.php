<?php

namespace TheCodingMachine\GraphQLite\Types;

use DateTimeImmutable;
use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Types;

class VoidTypeTest extends TestCase
{
    public function testSerialize(): void
    {
        self::assertNull(Types::void()->serialize(null));
    }

    public function testParseValue(): void
    {
        $this->expectExceptionObject(new GraphQLRuntimeException());

        Types::void()->parseValue(null);
    }

    public function testParseLiteral(): void
    {
        $this->expectExceptionObject(new GraphQLRuntimeException());

        Types::void()->parseLiteral(new NullValueNode([]));

    }
}
