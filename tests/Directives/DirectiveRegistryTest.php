<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\AuditFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\ReservedNameDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\VersionedInputObjectDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\UppercaseFieldDirective;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;

final class DirectiveRegistryTest extends TestCase
{
    public function testDiscoversAndRegistersValidDirectives(): void
    {
        $registry = self::buildRegistry([
            UppercaseFieldDirective::class,
            AuditFieldDirective::class,
            VersionedInputObjectDirective::class,
        ]);
        $registry->discover();

        $this->assertTrue($registry->hasAny());

        $webonyx = $registry->webonyxDirectives();
        $this->assertCount(3, $webonyx);

        $byName = [];
        foreach ($webonyx as $directive) {
            $byName[$directive->name] = $directive;
        }

        $this->assertArrayHasKey('uppercase', $byName);
        $this->assertArrayHasKey('audit', $byName);
        $this->assertArrayHasKey('versioned', $byName);

        $audit = $byName['audit'];
        $this->assertTrue($audit->isRepeatable);
        $this->assertSame('Marks a field as needing audit-log treatment.', $audit->description);
        $this->assertCount(1, $audit->args);
        $this->assertSame('reason', $audit->args[0]->name);
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
        $registry = self::buildRegistry([UppercaseFieldDirective::class]);
        $registry->discover();

        $names = array_map(static fn (WebonyxDirective $d) => $d->name, $registry->webonyxDirectives());
        $this->assertNotContains('skip', $names);
        $this->assertNotContains('include', $names);
        $this->assertNotContains('deprecated', $names);
        $this->assertNotContains('oneOf', $names);
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
