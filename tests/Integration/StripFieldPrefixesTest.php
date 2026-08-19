<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

/**
 * End-to-end verification of SchemaFactory::stripFieldPrefixes().
 *
 * The getter case: a #[SourceField(name: 'stock')] resolves against Product::hasStock() only once
 * "has" is registered as a getter prefix. The setter case: a #[Field] on a custom-prefixed setter
 * (StockAdjustment::assignDelta) defines and hydrates an input field once "assign" is a setter prefix.
 * The default prefixes leave both invisible (covered by the PropertyAccessor/NamingStrategy unit tests).
 */
class StripFieldPrefixesTest extends TestCase
{
    /**
     * @param list<string> $getters
     * @param list<string> $setters
     */
    private function buildSchema(string $namespace, array $getters = ['get', 'is'], array $setters = ['set']): Schema
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(new EmptyContainer()),
        );
        $factory->addNamespace($namespace);
        $factory->stripFieldPrefixes(getters: $getters, setters: $setters);

        return $factory->createSchema();
    }

    public function testHasserSourceFieldResolvesWhenGetterPrefixConfigured(): void
    {
        $schema = $this->buildSchema(
            'TheCodingMachine\\GraphQLite\\Fixtures\\StripFieldPrefixes',
            getters: ['get', 'is', 'has'],
        );

        $result = GraphQL::executeQuery(
            $schema,
            '
            query {
                product {
                    name
                    stock
                }
            }
            ',
        )->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame(['name' => 'Widget', 'stock' => true], $result['data']['product']);
    }

    public function testCustomSetterPrefixDefinesAndHydratesInputField(): void
    {
        $schema = $this->buildSchema(
            'TheCodingMachine\\GraphQLite\\Fixtures\\StripFieldPrefixesInput',
            setters: ['set', 'assign'],
        );

        $result = GraphQL::executeQuery(
            $schema,
            '
            mutation {
                adjustStock(adjustment: { delta: 5 })
            }
            ',
        )->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);

        // The input field is named "delta" (assign prefix stripped) and hydration calls assignDelta().
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame(5, $result['data']['adjustStock']);
    }
}
