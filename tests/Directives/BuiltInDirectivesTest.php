<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\Directives\CustomOneOfOverride;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;

use function array_map;

final class BuiltInDirectivesTest extends TestCase
{
    public function testRegistryAlwaysRegistersBuiltInAttributesEvenWithEmptyClassFinder(): void
    {
        $registry = self::buildRegistry([]);
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
    }

    public function testBuiltInDirectivesAreNotEmittedAsCustomDirectives(): void
    {
        $registry = self::buildRegistry([]);
        $registry->discover();

        // The registry must not contribute `@oneOf` to SchemaConfig::$directives — webonyx already
        // declares it as a built-in. Custom directive list stays empty.
        $names = array_map(static fn (WebonyxDirective $d) => $d->name, $registry->webonyxDirectives());

        $this->assertSame([], $names);
        $this->assertFalse($registry->hasAny());
    }

    public function testOneOfDefinitionMarksItselfAsBuiltIn(): void
    {
        $definition = OneOf::definition();

        $this->assertSame('oneOf', $definition->name);
        $this->assertTrue($definition->builtIn);
        $this->assertSame([DirectiveLocation::INPUT_OBJECT], $definition->locations);
    }

    public function testDiscoveryFindingTheBundledBuiltInClassIsIdempotent(): void
    {
        // Simulate a user namespace that happens to include our built-in attributes — the
        // class-finder yields the same FQCN that `BUILT_IN_ATTRIBUTES` registers. Registration
        // must not throw on the duplicate-class case.
        $registry = self::buildRegistry([OneOf::class]);
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
    }

    public function testUserOverrideOfBuiltInWinsOverBundled(): void
    {
        // A user supplies their own class binding to `@oneOf` (with `builtIn: true`). Our bundled
        // copy must defer so the user's behavior is the one that runs.
        $registry = self::buildRegistry([CustomOneOfOverride::class]);
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(CustomOneOfOverride::class));
        $this->assertNull($registry->definitionFor(OneOf::class));
    }

    /** @param list<class-string<TypeSystemDirective>> $classes */
    private static function buildRegistry(array $classes): DirectiveRegistry
    {
        $finder = new DirectiveClassFinder(
            new StaticClassFinder($classes),
            new HardClassFinderComputedCache(new Psr16Cache(new ArrayAdapter())),
        );

        return new DirectiveRegistry($finder);
    }
}
