<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;

use function array_values;

#[CoversClass(HardClassFinderComputedCache::class)]
class HardClassFinderComputedCacheTest extends TestCase
{
    public function testNotReusedCacheBecauseDifferentList(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $cache = new Psr16Cache($arrayAdapter);

        $classFinderComputedCache = new HardClassFinderComputedCache($cache);

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([
                FooType::class,
                FooExtendType::class,
                TestType::class,
            ]),
            'key',
            static fn (ReflectionClass $reflection) => $reflection->getShortName(),
            static fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        // Class finder have different class list - result should not be reused from the cache.
        // This is necessary to avoid caching issues when there're multiple class finders shares the same cache pool.
        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([FooType::class]),
            'key',
            static fn (ReflectionClass $reflection) => $reflection->getShortName(),
            static fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame(['FooType'], $result);
    }

    public function testReusedCacheBecauseSameList(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $cache = new Psr16Cache($arrayAdapter);

        $classFinderComputedCache = new HardClassFinderComputedCache($cache);

        $classList = [
            FooType::class,
            FooExtendType::class,
            TestType::class,
        ];
        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder($classList),
            'key',
            static fn (ReflectionClass $reflection) => $reflection->getShortName(),
            static fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        // Class finder have the same class list - even both functions have changed - the result should be cached.
        // This is useful in production, where code and file structure doesn't change.
        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder($classList),
            'key',
            static fn (ReflectionClass $reflection) => self::fail('Should not be called.'),
            static fn (array $entries) => self::fail('Should not be called.'),
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);
    }
}
