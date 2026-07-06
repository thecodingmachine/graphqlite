<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Introspection;
use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

use function array_filter;
use function assert;
use function count;
use function is_array;

/**
 * End-to-end test for custom directives. Builds a schema from the
 * `tests/Fixtures/DirectivesIntegration` namespaces and checks that directive definitions show up
 * in SDL and introspection, and that behavioral directives wrap their resolver at runtime.
 *
 * We don't assert directive applications in SDL (e.g. `tagline: String! @uppercase`): webonyx's
 * SchemaPrinter doesn't render those, and we follow its behavior. The applications are still on each
 * element's `astNode->directives` for anyone who wants to print them with their own printer.
 */
final class DirectivesEndToEndTest extends TestCase
{
    private function buildSchema(): Schema
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $factory = new SchemaFactory($cache, new BasicAutoWiringContainer(new EmptyContainer()));
        $factory->prodMode();
        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DirectivesIntegration');

        return $factory->createSchema();
    }

    public function testSchemaPrintsDirectiveDefinitions(): void
    {
        $schema = $this->buildSchema();
        $sdl = SchemaPrinter::doPrint($schema);

        // Definitions: the webonyx printer emits these once the directives are registered on the
        // schema (see SchemaFactory's wiring of DirectiveRegistry::webonyxDirectives()).
        $this->assertStringContainsString('directive @uppercase on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks a field for audit-log tracking.', $sdl);
        $this->assertStringContainsString('directive @audit(reason: String!) repeatable on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('directive @tagged(name: String!) on OBJECT', $sdl);
        $this->assertStringContainsString('directive @sanitized on INPUT_FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks an input with a schema version for backwards-compat tracking.', $sdl);
        $this->assertStringContainsString('directive @versioned(version: Int!) on INPUT_OBJECT', $sdl);

        // No assertions on directive applications here; webonyx's SchemaPrinter doesn't render them.
        // See the class docblock.

        // @oneOf is webonyx's built-in: it prints the application from the isOneOf flag, and we
        // don't re-declare it in the custom list.
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
        // getLabel() carries @uppercase, so "abc" comes back uppercased.
        $this->assertSame('ABC', $result['data']['findWidget']['label']);
    }

    public function testFieldDirectiveUppercasesMixedCaseValue(): void
    {
        $schema = $this->buildSchema();

        $result = GraphQL::executeQuery(
            $schema,
            '{ findWidget(lookup: { sku: "MixedCase" }) { label } }',
        )->toArray();

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertSame('MIXEDCASE', $result['data']['findWidget']['label']);
    }

    public function testRepeatableDirectiveAttachesOncePerUsageToFieldAst(): void
    {
        $widget = $this->buildSchema()->getType('Widget');
        assert($widget instanceof ObjectType);

        $astNode = $widget->getField('label')->astNode;
        $this->assertNotNull($astNode);

        $names = [];
        foreach ($astNode->directives as $directive) {
            $names[] = $directive->name->value;
        }

        // getLabel() carries @uppercase once and the repeatable @audit twice; every application
        // survives onto the field's AST node.
        $this->assertContains('uppercase', $names);
        $this->assertSame(2, count(array_filter($names, static fn (string $name) => $name === 'audit')));
    }
}
