<?php

namespace TheCodingMachine\GraphQLite\Types;

use DateTimeImmutable;
use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{

    public function testSerialize(): void
    {
        $dateTimeType = new DateTimeType();

        $this->assertSame('2019-05-05T10:10:10+00:00', $dateTimeType->serialize(new DateTimeImmutable('2019-05-05T10:10:10+00:00')));

        $this->expectException(InvariantViolation::class);
        $dateTimeType->serialize('foo');
    }

    public function testParseLiteral(): void
    {
        $dateTimeType = new DateTimeType();

        $this->assertSame('2019-05-05T10:10:10+00:00', $dateTimeType->parseLiteral(new StringValueNode(['value' => '2019-05-05T10:10:10+00:00'])));

        $this->expectException(Exception::class);
        $dateTimeType->parseLiteral(null);

    }

    public function testParseValue(): void
    {
        $dateTimeType = new DateTimeType();


        $this->assertNull($dateTimeType->parseValue(null));
    }
}
