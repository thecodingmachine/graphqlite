<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Validator\Rules;

use GraphQL\Error\Error;
use GraphQL\GraphQL;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Validator\DocumentValidator;
use PHPUnit\Framework\TestCase;

use function is_string;
use function preg_match;

class QueryComplexityTest extends TestCase
{
    public function testInvalidVariableIsRefusedByTheExecutorWithItsLocation(): void
    {
        $result = $this->execute(
            'query ($isbn: Isbn) { books(isbn: $isbn) { title } }',
            ['isbn' => 'abc'],
        )->toArray();

        $this->assertNull($result['data'] ?? null);
        $this->assertCount(1, $result['errors']);
        $this->assertSame(
            'Variable "$isbn" got invalid value "abc"; An ISBN has 13 digits.',
            $result['errors'][0]['message'],
        );
        $this->assertSame([['line' => 1, 'column' => 8]], $result['errors'][0]['locations']);
    }

    public function testEachInvalidVariableIsRefusedSeparately(): void
    {
        $result = $this->execute(
            'query ($first: Isbn, $second: Isbn) { a: books(isbn: $first) { title } b: books(isbn: $second) { title } }',
            ['first' => 'abc', 'second' => 'def'],
        )->toArray();

        $this->assertNull($result['data'] ?? null);
        $this->assertCount(2, $result['errors']);
        $this->assertStringStartsWith('Variable "$first" got invalid value "abc"', $result['errors'][0]['message']);
        $this->assertSame([['line' => 1, 'column' => 8]], $result['errors'][0]['locations']);
        $this->assertStringStartsWith('Variable "$second" got invalid value "def"', $result['errors'][1]['message']);
        $this->assertSame([['line' => 1, 'column' => 22]], $result['errors'][1]['locations']);
    }

    public function testValidOperationOverTheLimitIsRefused(): void
    {
        $result = $this->execute(
            'query ($isbn: Isbn, $take: Int) { books(isbn: $isbn, take: $take) { title } }',
            ['isbn' => '9780000000001', 'take' => 50],
        )->toArray();

        $this->assertNull($result['data'] ?? null);
        $this->assertSame('Max query complexity should be 20 but got 100.', $result['errors'][0]['message']);
    }

    public function testValidOperationUnderTheLimitIsExecuted(): void
    {
        $result = $this->execute(
            'query ($isbn: Isbn, $take: Int) { books(isbn: $isbn, take: $take) { title } }',
            ['isbn' => '9780000000001', 'take' => 5],
        )->toArray();

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame(['books' => [['title' => 'Title']]], $result['data']);
    }

    public function testUnrelatedPricingErrorIsNotSwallowed(): void
    {
        $result = $this->execute('{ unpriceable }')->toArray();

        $this->assertNull($result['data'] ?? null);
        $this->assertSame('Pricing failed.', $result['errors'][0]['message']);
    }

    public function testInvalidVariableOfAnotherOperationKeepsTheLimit(): void
    {
        $result = $this->execute(
            'query byIsbn($isbn: Isbn) { books(isbn: $isbn) { title } } query all { books { title } }',
            ['isbn' => 'abc'],
            'all',
        )->toArray();

        $this->assertNull($result['data'] ?? null);
        $this->assertStringStartsWith('Variable "$isbn" got invalid value "abc"', $result['errors'][0]['message']);
        $this->assertArrayNotHasKey('locations', $result['errors'][0]);
    }

    /** @param array<string, mixed> $variables */
    private function execute(string $query, array $variables = [], string|null $operationName = null): \GraphQL\Executor\ExecutionResult
    {
        return GraphQL::executeQuery(
            $this->createSchema(),
            $query,
            variableValues: $variables,
            operationName: $operationName,
            validationRules: [
                ...DocumentValidator::allRules(),
                new QueryComplexity(20),
            ],
        );
    }

    private function createSchema(): Schema
    {
        $isbn = new CustomScalarType([
            'name' => 'Isbn',
            'serialize' => static fn (mixed $value): mixed => $value,
            'parseValue' => static function (mixed $value): string {
                if (! is_string($value) || ! preg_match('/^\d{13}$/', $value)) {
                    throw new Error('An ISBN has 13 digits.');
                }

                return $value;
            },
            'parseLiteral' => static function (Node $node): string {
                if (! $node instanceof StringValueNode || ! preg_match('/^\d{13}$/', $node->value)) {
                    throw new Error('An ISBN has 13 digits.');
                }

                return $node->value;
            },
        ]);

        $book = new ObjectType([
            'name' => 'Book',
            'fields' => [
                'title' => ['type' => Type::string(), 'resolve' => static fn (): string => 'Title'],
            ],
        ]);

        return new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'books' => [
                        'type' => Type::listOf($book),
                        'args' => [
                            'isbn' => ['type' => $isbn],
                            'take' => ['type' => Type::int()],
                        ],
                        'complexity' => static fn (int $childrenComplexity, array $args): int => ($args['take'] ?? 1) * ($childrenComplexity + 1),
                        'resolve' => static fn (): array => [[]],
                    ],
                    'unpriceable' => [
                        'type' => Type::string(),
                        'complexity' => static fn (): int => throw new Error('Pricing failed.'),
                        'resolve' => static fn (): string => 'Resolved',
                    ],
                ],
            ]),
        ]);
    }
}
