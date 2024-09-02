<?php

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;

#[CoversClass(HardClassFinderComputedCache::class)]
class HardClassFinderComputedCacheTest extends TestCase
{
    public function testCachesResultOfReduceFunction(): void
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
            fn (\ReflectionClass $reflection) => $reflection->getShortName(),
            fn (array $entries) => [array_values($entries)],
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);

        // Even though the class finder and both functions have changed - the result should still be cached.
        // This is useful in production, where code and file structure doesn't change.
        [$result] = $classFinderComputedCache->compute(
            new StaticClassFinder([]),
            'key',
            fn (\ReflectionClass $reflection) => self::fail('Should not be called.'),
            fn (array $entries) => self::fail('Should not be called.'),
        );

        $this->assertSame([
            'FooType',
            'FooExtendType',
            'TestType',
        ], $result);
    }
}