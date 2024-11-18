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
use function clearstatcache;
use function Safe\filemtime;
use function Safe\touch;

#[CoversClass(SnapshotClassFinderComputedCache::class)]
class SnapshotClassFinderComputedCacheTest extends TestCase
{
    public function testCachesIndividualEntriesSameList(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $cache = new Psr16Cache($arrayAdapter);

        $classFinderComputedCache = new SnapshotClassFinderComputedCache($cache);

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

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder($classList),
            'key',
            static fn (ReflectionClass $reflection) => self::fail('Should not be called.'),
            static fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        $this->touch((new ReflectionClass(FooType::class))->getFileName());

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder($classList),
            'key',
            static fn (ReflectionClass $reflection) => $reflection->getShortName() . ' Modified',
            static fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType Modified',
            'FooExtendType',
            'TestType',
        ], $result);
    }

    private function touch(string $fileName): void
    {
        touch($fileName, filemtime($fileName) + 1);
        clearstatcache();
    }
}
