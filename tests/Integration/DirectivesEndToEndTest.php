<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\GraphQL;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Type\Definition\InputObjectType;
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
 * End-to-end custom directives: builds a schema from the `DirectivesIntegration` fixtures and checks
 * definitions (SDL/introspection), runtime behavior, and applications on each element's AST node.
 * webonyx's SchemaPrinter doesn't render applications, so those are checked via `astNode->directives`.
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

        $this->assertStringContainsString('directive @uppercase on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks a field for audit-log tracking.', $sdl);
        $this->assertStringContainsString('directive @audit(reason: String!) repeatable on FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('directive @tagged(name: String!) on OBJECT', $sdl);
        $this->assertStringContainsString('directive @sanitized on INPUT_FIELD_DEFINITION', $sdl);
        $this->assertStringContainsString('Marks an input with a schema version for backwards-compat tracking.', $sdl);
        $this->assertStringContainsString('directive @versioned(version: Int!) on INPUT_OBJECT', $sdl);

        // @oneOf is webonyx's: printed from the isOneOf flag, not re-declared in the custom list.
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

        // Built-ins present alongside custom directives.
        $this->assertContains('skip', $names);
        $this->assertContains('include', $names);
        $this->assertContains('deprecated', $names);

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
        // getLabel() carries @uppercase.
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

        // @uppercase once, repeatable @audit twice.
        $this->assertContains('uppercase', $names);
        $this->assertSame(2, count(array_filter($names, static fn (string $name) => $name === 'audit')));
    }

    public function testMetadataDirectivesAttachToTheirElementsAst(): void
    {
        $schema = $this->buildSchema();

        // Applications land on each element's AST node, though the printer omits them.
        $widget = $schema->getType('Widget');
        assert($widget instanceof ObjectType);
        $this->assertSame(['tagged' => ['name' => 'primary']], self::astDirectives($widget->astNode?->directives ?? []));

        $lookup = $schema->getType('WidgetLookupInput');
        assert($lookup instanceof InputObjectType);
        $this->assertSame(['versioned' => ['version' => '2']], self::astDirectives($lookup->astNode?->directives ?? []));

        $sku = $lookup->getField('sku')->astNode;
        $this->assertNotNull($sku);
        $this->assertSame(['sanitized' => []], self::astDirectives($sku->directives));
    }

    public function testOneOfInputEnforcesExactlyOneField(): void
    {
        $schema = $this->buildSchema();

        // One field resolves; @uppercase still applies to the label.
        $ok = GraphQL::executeQuery($schema, '{ findOneOf(lookup: { sku: "widget-1" }) { label } }')->toArray();
        $this->assertArrayNotHasKey('errors', $ok);
        $this->assertSame('WIDGET-1', $ok['data']['findOneOf']['label']);

        // Two fields violates @oneOf.
        $both = GraphQL::executeQuery($schema, '{ findOneOf(lookup: { sku: "a", id: 1 }) { label } }')->toArray();
        $this->assertArrayHasKey('errors', $both);
        $this->assertStringContainsString('exactly one field', $both['errors'][0]['message']);

        // Zero fields also violates @oneOf.
        $none = GraphQL::executeQuery($schema, '{ findOneOf(lookup: {}) { label } }')->toArray();
        $this->assertArrayHasKey('errors', $none);
    }

    /**
     * @param iterable<DirectiveNode> $directives
     *
     * @return array<string, array<string, string>> name => [arg => value]
     */
    private static function astDirectives(iterable $directives): array
    {
        $result = [];
        foreach ($directives as $directive) {
            $args = [];
            foreach ($directive->arguments as $argument) {
                $args[$argument->name->value] = $argument->value->value;
            }
            $result[$directive->name->value] = $args;
        }

        return $result;
    }
}
