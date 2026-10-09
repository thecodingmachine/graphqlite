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
        $this->expectUserDeprecationMessage(
            'QueryFieldDescriptor::withAddedDescriptionLines() is deprecated; use withAddedDescriptionItem() instead.',
        );

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

    public function testDescriptionItemsAreKeptApartFromTheDescription(): void
    {
        $resolver = fn () => null;

        $descriptor = (new QueryFieldDescriptor(
            'test',
            Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
            description: 'From the docblock',
        ))
            ->withAddedDescriptionItem('Audience: librarians')
            ->withAddedDescriptionItem('Cost: complexity = 5')
            ->withDescription('Explicit');

        self::assertSame('Explicit', $descriptor->getDescription());
        self::assertSame(['Audience: librarians', 'Cost: complexity = 5'], $descriptor->getDescriptionItems());
        self::assertSame(
            "Explicit\n\n- Audience: librarians\n- Cost: complexity = 5",
            QueryField::fromFieldDescriptor($descriptor)->description,
        );
    }
}
