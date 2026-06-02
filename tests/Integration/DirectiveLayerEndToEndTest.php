<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

/**
 * Checks the built-in directives flow through the directive middleware and reach the SDL: `#[OneOf]`
 * sets webonyx's `isOneOf` flag, and `#[Deprecated]` sets the field's deprecation reason.
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
}
