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

        // @oneOf is a webonyx built-in, so the registry shouldn't add it to the custom list.
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
        // If discovery finds our own built-in class (same FQCN as BUILT_IN_ATTRIBUTES), registering
        // it twice shouldn't throw.
        $registry = self::buildRegistry([OneOf::class]);
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
    }

    public function testUserOverrideOfBuiltInWinsOverBundled(): void
    {
        // User binds their own class to @oneOf (builtIn: true); ours should defer to it.
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
