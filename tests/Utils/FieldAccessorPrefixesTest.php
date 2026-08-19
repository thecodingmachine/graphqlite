<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FieldAccessorPrefixesTest extends TestCase
{
    #[DataProvider('stripGetterProvider')]
    public function testStripGetterPrefix(string $expected, string $methodName, FieldAccessorPrefixes $prefixes): void
    {
        self::assertSame($expected, $prefixes->stripGetterPrefix($methodName));
    }

    public static function stripGetterProvider(): iterable
    {
        $default = new FieldAccessorPrefixes();
        $withHas = new FieldAccessorPrefixes(getters: ['get', 'is', 'has']);

        // Default getters, genuine accessors on a camelCase boundary.
        yield 'getName' => ['name', 'getName', $default];
        yield 'isEnabled' => ['enabled', 'isEnabled', $default];
        // Bare prefix (nothing after it) is left alone.
        yield 'get' => ['get', 'get', $default];
        yield 'is' => ['is', 'is', $default];
        // No prefix.
        yield 'foo' => ['foo', 'foo', $default];
        // Words that merely start with the prefix letters (next char lowercase) are NOT stripped.
        yield 'issue' => ['issue', 'issue', $default];
        yield 'getaway' => ['getaway', 'getaway', $default];
        yield 'gettext' => ['gettext', 'gettext', $default];
        // Non-letter right after the prefix is not a boundary either.
        yield 'get2FA' => ['get2FA', 'get2FA', $default];
        yield 'get_foo' => ['get_foo', 'get_foo', $default];
        // "has" only strips once configured.
        yield 'hasAccess default' => ['hasAccess', 'hasAccess', $default];
        yield 'hasAccess with has' => ['access', 'hasAccess', $withHas];
        yield 'hasHKey with has' => ['hKey', 'hasHKey', $withHas];
        yield 'hashKey with has' => ['hashKey', 'hashKey', $withHas];
    }

    #[DataProvider('stripSetterProvider')]
    public function testStripSetterPrefix(string $expected, string $methodName, FieldAccessorPrefixes $prefixes): void
    {
        self::assertSame($expected, $prefixes->stripSetterPrefix($methodName));
    }

    public static function stripSetterProvider(): iterable
    {
        $default = new FieldAccessorPrefixes();
        $withAssign = new FieldAccessorPrefixes(setters: ['assign']);

        yield 'setName' => ['name', 'setName', $default];
        yield 'set' => ['set', 'set', $default];
        // "settings"/"setup" merely start with "set"; they are not setters.
        yield 'settings' => ['settings', 'settings', $default];
        yield 'setup' => ['setup', 'setup', $default];
        // Custom setter prefix.
        yield 'assignName with assign' => ['name', 'assignName', $withAssign];
        yield 'assignup with assign' => ['assignup', 'assignup', $withAssign];
        // A default "set" method is not a setter once the prefix list no longer contains "set".
        yield 'setName with assign only' => ['setName', 'setName', $withAssign];
    }

    #[DataProvider('hasSetterPrefixProvider')]
    public function testHasSetterPrefix(bool $expected, string $methodName, FieldAccessorPrefixes $prefixes): void
    {
        self::assertSame($expected, $prefixes->hasSetterPrefix($methodName));
    }

    public static function hasSetterPrefixProvider(): iterable
    {
        $default = new FieldAccessorPrefixes();
        $withAssign = new FieldAccessorPrefixes(setters: ['assign']);

        yield 'setName is a setter' => [true, 'setName', $default];
        yield 'settings is not a setter' => [false, 'settings', $default];
        yield 'bare set is not a setter' => [false, 'set', $default];
        yield 'assignName under assign' => [true, 'assignName', $withAssign];
        // The footgun guard: with "set" removed from the setter list, a set* method is not a setter,
        // so input-field discovery skips it instead of deriving a broken field name.
        yield 'setName under assign only' => [false, 'setName', $withAssign];
    }

    public function testEmptyMethodNameAndPrefixEqualToMethodAreSafe(): void
    {
        $prefixes = new FieldAccessorPrefixes();

        // Prefix equal to (or longer than) the whole method name never strips and never over-reads.
        self::assertSame('get', $prefixes->stripGetterPrefix('get'));
        self::assertSame('', $prefixes->stripGetterPrefix(''));
        self::assertFalse($prefixes->hasSetterPrefix('set'));
    }
}
