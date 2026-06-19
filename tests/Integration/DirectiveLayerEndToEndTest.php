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
    private function buildSchema(): Schema
    {
        $factory = new SchemaFactory(new Psr16Cache(new ArrayAdapter()), new BasicAutoWiringContainer(new EmptyContainer()));
        $factory->prodMode();
        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DirectiveLayerIntegration');

        return $factory->createSchema();
    }

    /** @return array<string, mixed> */
    private function execute(Schema $schema, string $query): array
    {
        return GraphQL::executeQuery($schema, $query, null, new Context())
            ->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE);
    }

    public function testOneOfPrintsInSdl(): void
    {
        $sdl = SchemaPrinter::doPrint($this->buildSchema());

        $this->assertStringContainsString('input LookupInput @oneOf', $sdl);
    }

    public function testDeprecatedPrintsInSdl(): void
    {
        $sdl = SchemaPrinter::doPrint($this->buildSchema());

        $this->assertStringContainsString('legacy: String! @deprecated(reason: "Use current instead.")', $sdl);
    }

    public function testBareDeprecatedKeepsTheDocblockReason(): void
    {
        $sdl = SchemaPrinter::doPrint($this->buildSchema());

        $this->assertStringContainsString('legacyDocblock: String! @deprecated(reason: "Use lookup instead.")', $sdl);
    }

    public function testDeprecatedFieldStillResolves(): void
    {
        $result = $this->execute($this->buildSchema(), 'query { legacy }');

        $this->assertSame(['data' => ['legacy' => 'legacy']], $result);
    }

    public function testOneOfAcceptsExactlyOneField(): void
    {
        $result = $this->execute($this->buildSchema(), 'query { lookup(lookup: {sku: "abc"}) }');

        $this->assertSame(['data' => ['lookup' => 'abc']], $result);
    }

    public function testOneOfRejectsMoreThanOneField(): void
    {
        $result = $this->execute($this->buildSchema(), 'query { lookup(lookup: {sku: "abc", id: 1}) }');

        $this->assertArrayNotHasKey('data', $result);
        $this->assertStringContainsString(
            "OneOf input object 'LookupInput' must specify exactly one field",
            $result['errors'][0]['message'],
        );
    }

    public function testOneOfRejectsZeroFields(): void
    {
        $result = $this->execute($this->buildSchema(), 'query { lookup(lookup: {}) }');

        $this->assertArrayNotHasKey('data', $result);
        $this->assertStringContainsString(
            "OneOf input object 'LookupInput' must specify exactly one field",
            $result['errors'][0]['message'],
        );
    }
}
