<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;

class QueryFieldDescriptorTest extends TestCase
{
    #[DataProvider('withAddedDescriptionLineProvider')]
    public function testWithAddedDescriptionLine(
        string $expected,
        string|null $previous,
        string $added,
    ): void
    {
        $resolver = fn () => null;

        $descriptor = (new QueryFieldDescriptor(
            'test',
            Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
            description: $previous,
        ))->withAddedDescriptionLines($added);

        self::assertSame($expected, $descriptor->getDescription());
    }

    public static function withAddedDescriptionLineProvider(): iterable
    {
        yield ['', null, ''];
        yield ['Asd', null, 'Asd'];
        yield ["Some description\nAsd", 'Some description', 'Asd'];
    }
}
