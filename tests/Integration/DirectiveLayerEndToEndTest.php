<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

/**
 * Drives the built-in directives end to end: through the directive middleware, into the SDL, and
 * through query execution. `#[OneOf]` sets webonyx's `isOneOf` flag (validated at runtime) and
 * `#[Deprecated]` sets the field's deprecation reason.
 */
final class DirectiveLayerEndToEndTest extends TestCase
{
    private Schema $schema;

    public function setUp(): void
    {
        $factory = new SchemaFactory(new Psr16Cache(new ArrayAdapter()), new BasicAutoWiringContainer(new EmptyContainer()));
        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DirectiveLayerIntegration');

        $this->schema = $factory->createSchema();
    }

    /** @return array<string, mixed> */
    private function execute(string $query): array
    {
        return GraphQL::executeQuery($this->schema, $query, null, new Context())
            ->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE);
    }

    public function testOneOfPrintsInSdl(): void
    {
        $sdl = SchemaPrinter::doPrint($this->schema);

        $this->assertStringContainsString('input LookupInput @oneOf', $sdl);
    }

    public function testDeprecatedPrintsInSdl(): void
    {
        $sdl = SchemaPrinter::doPrint($this->schema);

        $this->assertStringContainsString('legacy: String! @deprecated(reason: "Use current instead.")', $sdl);
    }

    public function testBareDeprecatedKeepsTheDocblockReason(): void
    {
        $sdl = SchemaPrinter::doPrint($this->schema);

        $this->assertStringContainsString('legacyDocblock: String! @deprecated(reason: "Use lookup instead.")', $sdl);
    }

    public function testDeprecatedFieldStillResolves(): void
    {
        $result = $this->execute('query { legacy }');

        $this->assertSame(['data' => ['legacy' => 'legacy']], $result);
    }

    /**
     * The deprecation reason is only observable at runtime through introspection (a deprecated field
     * still returns its normal value). Asserting the introspection response proves the directive both
     * applies the reason and leaves introspection working.
     */
    public function testDeprecationIsExposedThroughIntrospection(): void
    {
        $query = '
        query {
            __type(name: "Query") {
                fields(includeDeprecated: true) {
                    name
                    isDeprecated
                    deprecationReason
                }
            }
        }
        ';

        $result = $this->execute($query);

        $this->assertSame([
            'data' => [
                '__type' => [
                    'fields' => [
                        ['name' => 'legacy', 'isDeprecated' => true, 'deprecationReason' => 'Use current instead.'],
                        ['name' => 'lookup', 'isDeprecated' => false, 'deprecationReason' => null],
                        ['name' => 'legacyDocblock', 'isDeprecated' => true, 'deprecationReason' => 'Use lookup instead.'],
                    ],
                ],
            ],
        ], $result);
    }

    public function testOneOfAcceptsExactlyOneField(): void
    {
        $result = $this->execute('query { lookup(lookup: {sku: "abc"}) }');

        $this->assertSame(['data' => ['lookup' => 'abc']], $result);
    }

    public function testOneOfRejectsMoreThanOneField(): void
    {
        $result = $this->execute('query { lookup(lookup: {sku: "abc", id: 1}) }');

        $this->assertArrayNotHasKey('data', $result);
        $this->assertStringContainsString(
            "OneOf input object 'LookupInput' must specify exactly one field",
            $result['errors'][0]['message'],
        );
    }

    public function testOneOfRejectsZeroFields(): void
    {
        $result = $this->execute('query { lookup(lookup: {}) }');

        $this->assertArrayNotHasKey('data', $result);
        $this->assertStringContainsString(
            "OneOf input object 'LookupInput' must specify exactly one field",
            $result['errors'][0]['message'],
        );
    }
}
