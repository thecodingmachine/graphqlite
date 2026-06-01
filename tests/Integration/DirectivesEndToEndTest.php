<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\GraphQL;
use GraphQL\Type\Introspection;
use TheCodingMachine\GraphQLite\Utils\SchemaPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\SchemaFactory;

use function assert;
use function is_array;

/**
 * End-to-end verification of custom directive support. Boots a schema via {@see SchemaFactory}
 * against the `tests/Fixtures/DirectivesIntegration` namespaces, then asserts:
 *
 *   - SDL emits the directive definitions (`directive @uppercase on FIELD_DEFINITION`, ...).
 *   - SDL emits the directive applications (`tagline: String! @uppercase`, ...).
 *   - Introspection reports each custom directive alongside the webonyx built-ins.
 *   - A field carrying `@uppercase` actually has its resolver wrapped at runtime.
 */
final class DirectivesEndToEndTest extends TestCase
{
    private function buildSchema(): \TheCodingMachine\GraphQLite\Schema
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $factory = new SchemaFactory($cache, new BasicAutoWiringContainer(new EmptyContainer()));
        $factory->prodMode();
        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DirectivesIntegration');

        return $factory->createSchema();
    }

    public function testSchemaPrintsDirectiveDefinitionsAndApplications(): void
    {
        $schema = $this->buildSchema();
        $sdl = SchemaPrinter::doPrint($schema);

        // Definitions
        $this->assertStringContainsString('directive @uppercase on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks a field for audit-log tracking.', $sdl);
        $this->assertStringContainsString('directive @audit(reason: String!) repeatable on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('directive @tagged(name: String!) on OBJECT', $sdl);
        $this->assertStringContainsString('directive @sanitized on INPUT_FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks an input with a schema version for backwards-compat tracking.', $sdl);
        $this->assertStringContainsString('directive @versioned(version: Int!) on INPUT_OBJECT', $sdl);

        // Applications
        $this->assertStringContainsString('@uppercase', $sdl);
        $this->assertStringContainsString('@audit(reason: "pii")', $sdl);
        $this->assertStringContainsString('@audit(reason: "compliance")', $sdl);
        $this->assertStringContainsString('@tagged(name: "primary")', $sdl);
        $this->assertStringContainsString('@versioned(version: 2)', $sdl);
        $this->assertStringContainsString('@sanitized', $sdl);

        // `#[OneOf]` binds PHP behavior to webonyx's built-in `@oneOf` directive — webonyx prints
        // the application from its own `isOneOf` flag, and we must NOT re-declare the directive
        // alongside the custom list.
        $this->assertStringNotContainsString('directive @oneOf ', $sdl);
        $this->assertStringContainsString('input OneOfLookupInput @oneOf', $sdl);
    }

    public function testIntrospectionExposesEveryDirective(): void
    {
        $schema = $this->buildSchema();

        $result = GraphQL::executeQuery($schema, Introspection::getIntrospectionQuery())->toArray();
        $this->assertArrayNotHasKey('errors', $result);

        $directives = $result['data']['__schema']['directives'];
        assert(is_array($directives));

        $names = [];
        foreach ($directives as $directive) {
            $names[] = $directive['name'];
        }

        // Built-ins remain present alongside custom directives.
        $this->assertContains('skip', $names);
        $this->assertContains('include', $names);
        $this->assertContains('deprecated', $names);

        // Custom directives present.
        $this->assertContains('uppercase', $names);
        $this->assertContains('audit', $names);
        $this->assertContains('tagged', $names);
        $this->assertContains('versioned', $names);
        $this->assertContains('sanitized', $names);
    }

    public function testFieldDirectiveWrapsResolver(): void
    {
        $schema = $this->buildSchema();

        $result = GraphQL::executeQuery($schema, '{ tagline }')->toArray();
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame('HELLO WORLD', $result['data']['tagline']);
    }

    public function testInputObjectFieldsResolveWithDirectiveAttached(): void
    {
        $schema = $this->buildSchema();

        $result = GraphQL::executeQuery(
            $schema,
            '{ findWidget(lookup: { sku: "abc" }) { label } }',
        )->toArray();

        $this->assertArrayNotHasKey('errors', $result);
        // The UppercaseDirective on getLabel() should uppercase the returned string.
        $this->assertSame('ABC', $result['data']['findWidget']['label']);
    }
}
