<?php

namespace TheCodingMachine\GraphQLite\Tests\Types;

use DateTimeImmutable;
use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Types;
use TheCodingMachine\GraphQLite\Types\VoidType;

class VoidTypeTest extends TestCase
{
    public function testSerialize(): void
    {
        self::assertNull((new VoidType())->serialize(null));
    }

    public function testParseValue(): void
    {
        $this->expectExceptionObject(new GraphQLRuntimeException());

        (new VoidType())->parseValue(null);
    }

    public function testParseLiteral(): void
    {
        $this->expectExceptionObject(new GraphQLRuntimeException());

        (new VoidType())->parseLiteral(new NullValueNode([]));
    }
}
