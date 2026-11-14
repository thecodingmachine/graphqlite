<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function file_get_contents;
use function json_decode;
use function preg_match_all;
use function str_starts_with;

use const JSON_THROW_ON_ERROR;

class TestNamespaceTest extends TestCase
{
    public function testComposerUsesDedicatedTestNamespace(): void
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $autoload = $composer['autoload-dev']['psr-4'];

        self::assertSame('tests/', $autoload['TheCodingMachine\\GraphQLite\\Tests\\'] ?? null);
        self::assertArrayNotHasKey('TheCodingMachine\\GraphQLite\\', $autoload);
    }

    public function testPhpFilesUseDedicatedTestNamespace(): void
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all('/^\s*namespace\s+([^;]+);/m', file_get_contents($file->getPathname()), $matches);
            foreach ($matches[1] as $namespace) {
                self::assertTrue(
                    $namespace === __NAMESPACE__ || str_starts_with($namespace, __NAMESPACE__ . '\\'),
                    $file->getPathname() . ' uses namespace ' . $namespace,
                );
            }
        }
    }
}
