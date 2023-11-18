<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use stdClass;

class QueryFieldDescriptorTest extends TestCase
{
    public function testExceptionInGetOriginalResolver(): void
    {
        $descriptor = new QueryFieldDescriptor('test', Type::string());
        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->getOriginalResolver();
    }

    /**
     * @dataProvider withAddedCommentLineProvider
     */
    public function testWithAddedCommentLine(string $expected, string|null $previous, string $added): void
    {
        $descriptor = (new QueryFieldDescriptor(
            'test',
            Type::string(),
            comment: $previous,
        ))->withAddedCommentLines($added);

        self::assertSame($expected, $descriptor->getComment());
    }

    public static function withAddedCommentLineProvider(): iterable
    {
        yield ['', null, ''];
        yield ['Asd', null, 'Asd'];
        yield ["Some comment\nAsd", 'Some comment', 'Asd'];
    }
}
