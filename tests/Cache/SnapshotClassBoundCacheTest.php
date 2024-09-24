<?php

namespace TheCodingMachine\GraphQLite\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\NoTypeAnnotation;

use function Safe\touch;
use function Safe\filemtime;

#[CoversClass(SnapshotClassBoundCache::class)]
class SnapshotClassBoundCacheTest extends TestCase
{
    public function testFirstGetsItemFromResolverThenFromCache(): void
    {
        $arrayCache = new ArrayAdapter();
        $classBoundCache = new SnapshotClassBoundCache(
            new Psr16Cache($arrayCache),
            FilesSnapshot::forClass(...),
        );

        $fooReflection = new \ReflectionClass(FooType::class);
        $fooKeyResult = $classBoundCache->get($fooReflection, fn () => 'foo_key', 'key', true);

        self::assertSame('foo_key', $fooKeyResult);
        self::assertSame('foo_key',  $classBoundCache->get($fooReflection, fn () => self::fail('Should not be called.'), 'key', true));

        $fooDifferentKeyResult = $classBoundCache->get($fooReflection, fn () => 'foo_different_key', 'different_key', true);

        self::assertSame('foo_different_key', $fooDifferentKeyResult);
        self::assertSame('foo_different_key',  $classBoundCache->get($fooReflection, fn () => self::fail('Should not be called.'), 'different_key', true));

        $barReflection = new \ReflectionClass(NoTypeAnnotation::class);
        $barKeyResult = $classBoundCache->get($barReflection, fn () => 'bar_key', 'key', true);

        self::assertSame('bar_key', $barKeyResult);
        self::assertSame('bar_key',  $classBoundCache->get($barReflection, fn () => self::fail('Should not be called.'), 'key', true));

        self::assertCount(3, $arrayCache->getValues());

        $this->touch($fooReflection->getParentClass()->getFileName());

        self::assertSame(
            'foo_key_updated',
            $classBoundCache->get($fooReflection, fn () => 'foo_key_updated', 'key', true)
        );
    }

    private function touch(string $fileName): void
    {
        touch($fileName, filemtime($fileName) + 1);
        clearstatcache();
    }
}