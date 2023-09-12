<?php

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\QueryComplexity;
use TheCodingMachine\GraphQLite\Schema;

class QueryComplexityTest extends IntegrationTestCase
{
    public function testDoesNotExceedMaxQueryComplexity(): void
    {
        $schema = $this->mainContainer->get(Schema::class);
        assert($schema instanceof Schema);

        $result = GraphQL::executeQuery(
            $schema,
            <<<'GRAPHQL'
                query {
                    articles {
                        title
                    }
                }
            GRAPHQL,
            validationRules: [
                ...DocumentValidator::allRules(),
                new QueryComplexity(60),
            ]
        );

        $this->assertSame([
            ['title' => 'Title']
        ], $this->getSuccessResult($result)['articles']);
    }

    public function testExceedsAllowedQueryComplexity(): void
    {
        $schema = $this->mainContainer->get(Schema::class);
        assert($schema instanceof Schema);

        $result = GraphQL::executeQuery(
            $schema,
            <<<'GRAPHQL'
                query {
                    articles {
                        title
                    }
                }
            GRAPHQL,
            validationRules: [
                ...DocumentValidator::allRules(),
                new QueryComplexity(5),
            ]
        );

        $this->assertSame('Max query complexity should be 5 but got 60.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
    }

    /**
     * @dataProvider calculatesCorrectQueryCostProvider
     */
    public function testCalculatesCorrectQueryCost(int $expectedCost, string $query): void
    {
        $schema = $this->mainContainer->get(Schema::class);
        assert($schema instanceof Schema);

        $result = GraphQL::executeQuery(
            $schema,
            $query,
            validationRules: [
                ...DocumentValidator::allRules(),
                new QueryComplexity(1),
            ]
        );

        $this->assertSame('Max query complexity should be 1 but got ' . $expectedCost . '.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
    }

    public static function calculatesCorrectQueryCostProvider(): iterable
    {
        yield [
            60, // 10 articles * (5 in controller + 1 for title)
            <<<'GRAPHQL'
                query {
                    articles {
                        title
                    }
                }
            GRAPHQL,
        ];

        yield [
            110, // 10 articles * (5 in controller + 1 for title + 5 for description)
            <<<'GRAPHQL'
                query {
                    articles {
                        title
                        comment
                    }
                }
            GRAPHQL,
        ];

        yield [
            100, // 10 articles * (5 in controller + 1 for title + 3 for author + 1 for author name)
            <<<'GRAPHQL'
                query {
                    articles {
                        title
                        author {
                            name
                        }
                    }
                }
            GRAPHQL,
        ];

        yield [
            18, // 3 articles * (5 in controller + 1 for title)
            <<<'GRAPHQL'
                query {
                    articles(take: 3) {
                        title
                    }
                }
            GRAPHQL,
        ];

        yield [
            3000, // 500 articles default multiplier * (5 in controller + 1 for title)
            <<<'GRAPHQL'
                query {
                    articles(take: null) {
                        title
                    }
                }
            GRAPHQL,
        ];
    }

    /**
     * @dataProvider reportsQueryCostInIntrospectionProvider
     */
    public function testReportsQueryCostInIntrospection(string|null $expectedDescription, string $typeName, string $fieldName): void
    {
        $schema = $this->mainContainer->get(Schema::class);
        assert($schema instanceof Schema);

        $result = GraphQL::executeQuery(
            $schema,
            <<<'GRAPHQL'
                query fieldDescription($type: String!) {
                    __type(name: $type) {
                        fields {
                            name
                            description
                        }
                    }
                }
            GRAPHQL,
            variableValues: ['type' => $typeName],
        );

        $data = $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS);

        $fieldsByName = array_filter($data['data']['__type']['fields'], fn (array $field) => $field['name'] === $fieldName);
        $fieldByName = reset($fieldsByName);

        self::assertNotNull($fieldByName);
        self::assertSame($expectedDescription, $fieldByName['description']);
    }

    public static function reportsQueryCostInIntrospectionProvider(): iterable
    {
        yield [
            'Cost: complexity = 5, multipliers = [take], defaultMultiplier = 500',
            'Query',
            'articles',
        ];

        yield [
            null,
            'Post',
            'title',
        ];

        yield [
            'Cost: complexity = 5, multipliers = [], defaultMultiplier = null',
            'Post',
            'comment',
        ];

        yield [
            'Cost: complexity = 3, multipliers = [], defaultMultiplier = null',
            'Post',
            'author',
        ];
    }
}