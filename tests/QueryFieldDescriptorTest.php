<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;

class QueryFieldDescriptorTest extends TestCase
{
    #[DataProvider('withAddedCommentLineProvider')]
    public function testWithAddedCommentLine(string $expected, string|null $previous, string $added): void
    {
        $resolver = fn () => null;

        $descriptor = (new QueryFieldDescriptor(
            'test',
            Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
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
