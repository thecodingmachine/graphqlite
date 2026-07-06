<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\Deprecated;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\Directives\InternalFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\ReservedNameDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\MisusedOneOfOnType;
use TheCodingMachine\GraphQLite\Fixtures\Directives\NoteFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\RevisionInputObjectDirective;

use function array_map;

final class DirectiveRegistryTest extends TestCase
{
    public function testRegistersTheBuiltInDirectives(): void
    {
        $registry = $this->registry();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
        $this->assertNotNull($registry->definitionFor(Deprecated::class));
    }

    public function testBuiltInsAreNotAddedToTheSchemaDirectiveList(): void
    {
        // webonyx already declares @oneOf and @deprecated, so the registry contributes nothing to
        // SchemaConfig::$directives.
        $registry = $this->registry();

        $this->assertSame([], $registry->webonyxDirectives());
        $this->assertFalse($registry->hasAny());
    }

    public function testResolvesBuiltInArguments(): void
    {
        $arguments = $this->registry()->argumentsFor(Deprecated::class);

        $this->assertCount(1, $arguments);
        $this->assertSame('reason', $arguments[0]->name);
    }

    public function testDiscoverIsIdempotent(): void
    {
        $registry = self::buildRegistry();
        $registry->discover();
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
    }

    public function testObjectTypeDirectivesRejectsMisplacedDirective(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/cannot be used on OBJECT/');

        $this->registry()->objectTypeDirectives(new ReflectionClass(MisusedOneOfOnType::class));
    }

    public function testDiscoversAndRegistersValidDirectives(): void
    {
        $registry = self::buildRegistry([
            InternalFieldDirective::class,
            NoteFieldDirective::class,
            RevisionInputObjectDirective::class,
        ]);
        $registry->discover();

        $this->assertTrue($registry->hasAny());

        $webonyx = $registry->webonyxDirectives();
        $this->assertCount(3, $webonyx);

        $byName = [];
        foreach ($webonyx as $directive) {
            $byName[$directive->name] = $directive;
        }

        $this->assertArrayHasKey('internal', $byName);
        $this->assertArrayHasKey('note', $byName);
        $this->assertArrayHasKey('revision', $byName);

        $note = $byName['note'];
        $this->assertTrue($note->isRepeatable);
        $this->assertSame('Attaches a freeform note to a field.', $note->description);
        $this->assertCount(1, $note->args);
        $this->assertSame('text', $note->args[0]->name);
    }

    public function testRejectsCustomDirectiveUsingReservedName(): void
    {
        $registry = self::buildRegistry([ReservedNameDirective::class]);

        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/reserved/');

        $registry->discover();
    }

    public function testEmptyRegistryReportsNoDirectives(): void
    {
        $registry = self::buildRegistry([]);
        $registry->discover();

        $this->assertFalse($registry->hasAny());
        $this->assertSame([], $registry->webonyxDirectives());
    }

    public function testWebonyxDirectivesIsolatedFromBuiltins(): void
    {
        // The built directives shouldn't include webonyx's built-ins; those get merged in at the
        // Schema layer, not here.
        $registry = self::buildRegistry([InternalFieldDirective::class]);
        $registry->discover();

        $names = array_map(static fn (WebonyxDirective $d) => $d->name, $registry->webonyxDirectives());
        $this->assertNotContains('skip', $names);
        $this->assertNotContains('include', $names);
        $this->assertNotContains('deprecated', $names);
        $this->assertNotContains('oneOf', $names);
    }

    private function registry(): DirectiveRegistry
    {
        $registry = self::buildRegistry();
        $registry->discover();

        return $registry;
    }

    /** @param list<class-string<TypeSystemDirective>> $classes */
    private static function buildRegistry(array $classes = []): DirectiveRegistry
    {
        $finder = new DirectiveClassFinder(
            new StaticClassFinder($classes),
            new HardClassFinderComputedCache(new Psr16Cache(new ArrayAdapter())),
        );

        return new DirectiveRegistry(new AnnotationReader(), $finder);
    }
}
