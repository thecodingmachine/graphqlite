<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DescriptionResolverTest extends TestCase
{
    #[DataProvider('provideResolveCases')]
    public function testResolve(
        string|null $expected,
        bool $useDocblockFallback,
        string|null $explicit,
        string|null $docblockDerived,
    ): void {
        $resolver = new DescriptionResolver($useDocblockFallback);

        self::assertSame($expected, $resolver->resolve($explicit, $docblockDerived));
    }

    public static function provideResolveCases(): iterable
    {
        // Precedence matrix — covers every combination of explicit value, docblock value,
        // and the SchemaFactory-level fallback toggle.

        yield 'explicit non-empty wins over docblock (fallback on)' => [
            'expected' => 'Explicit wins',
            'useDocblockFallback' => true,
            'explicit' => 'Explicit wins',
            'docblockDerived' => 'From docblock',
        ];

        yield 'explicit non-empty wins when fallback off' => [
            'expected' => 'Explicit only',
            'useDocblockFallback' => false,
            'explicit' => 'Explicit only',
            'docblockDerived' => 'Would be ignored anyway',
        ];

        yield 'explicit empty string blocks docblock fallback' => [
            'expected' => '',
            'useDocblockFallback' => true,
            'explicit' => '',
            'docblockDerived' => 'This docblock must NOT leak',
        ];

        yield 'null explicit falls through to docblock when fallback on' => [
            'expected' => 'From docblock',
            'useDocblockFallback' => true,
            'explicit' => null,
            'docblockDerived' => 'From docblock',
        ];

        yield 'null explicit returns null when fallback off' => [
            'expected' => null,
            'useDocblockFallback' => false,
            'explicit' => null,
            'docblockDerived' => 'Ignored because fallback disabled',
        ];

        yield 'null explicit + null docblock returns null' => [
            'expected' => null,
            'useDocblockFallback' => true,
            'explicit' => null,
            'docblockDerived' => null,
        ];

        yield 'null explicit + empty docblock returns empty (caller passes through)' => [
            'expected' => '',
            'useDocblockFallback' => true,
            'explicit' => null,
            'docblockDerived' => '',
        ];
    }

    public function testIsDocblockFallbackEnabledReflectsConstructor(): void
    {
        self::assertTrue((new DescriptionResolver(true))->isDocblockFallbackEnabled());
        self::assertFalse((new DescriptionResolver(false))->isDocblockFallbackEnabled());
    }
}
