<?php

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\EnumType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;

use function Safe\touch;
use function Safe\filemtime;

#[CoversClass(SnapshotClassFinderComputedCache::class)]
class SnapshotClassFinderComputedCacheTest extends TestCase
{
    public function testCachesIndividualEntries(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $cache = new Psr16Cache($arrayAdapter);

        $classFinderComputedCache = new SnapshotClassFinderComputedCache($cache);

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([
                FooType::class,
                FooExtendType::class,
                TestType::class,
            ]),
            'key',
            fn (\ReflectionClass $reflection) => $reflection->getShortName(),
            fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([
                FooType::class,
                FooExtendType::class,
                TestType::class,
            ]),
            'key',
            fn (\ReflectionClass $reflection) => self::fail('Should not be called.'),
            fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        $this->touch((new \ReflectionClass(FooType::class))->getFileName());

        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([
                FooType::class,
                TestType::class,
                EnumType::class,
            ]),
            'key',
            fn (\ReflectionClass $reflection) => $reflection->getShortName() . ' Modified',
            fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType Modified',
            'TestType',
            'EnumType Modified',
        ], $result);
    }

    private function touch(string $fileName): void
    {
        touch($fileName, filemtime($fileName) + 1);
        clearstatcache();
    }
}