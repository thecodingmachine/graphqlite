<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Server\OperationParams;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CachePersistedQueryLoaderTest extends TestCase
{
    private const QUERY_STRING = 'query { field }';
    private const QUERY_HASH = '7b82cd908482825da2a4381cdda62a1384faa0c1b4c248e086aa44aa59fb9cd8';

    private Psr16Cache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new Psr16Cache(new ArrayAdapter());
    }

    public function testReturnsQueryFromCache(): void
    {
        $loader = new CachePersistedQueryLoader($this->cache);

        $this->cache->set(self::QUERY_HASH, self::QUERY_STRING);

        self::assertSame(self::QUERY_STRING, $loader(self::QUERY_HASH, OperationParams::create([])));
        self::assertSame(self::QUERY_STRING, $loader(strtoupper(self::QUERY_HASH), OperationParams::create([])));
    }

    public function testSavesQueryIntoCache(): void
    {
        $loader = new CachePersistedQueryLoader($this->cache);

        self::assertSame(self::QUERY_STRING, $loader(self::QUERY_HASH, OperationParams::create([
            'query' => self::QUERY_STRING,
        ])));
        self::assertTrue($this->cache->has(self::QUERY_HASH));
        self::assertSame(self::QUERY_STRING, $this->cache->get(self::QUERY_HASH));
    }

    public function testThrowsNotFoundExceptionWhenQueryNotFound(): void
    {
        $this->expectException(PersistedQueryNotFoundException::class);
        $this->expectExceptionMessage('Persisted query by that ID was not found and "query" was omitted.');

        $loader = new CachePersistedQueryLoader($this->cache);

        $loader('asd', OperationParams::create([]));
    }

    public function testThrowsIdInvalidExceptionWhenQueryDoesNotMatchId(): void
    {
        $this->expectException(PersistedQueryIdInvalidException::class);
        $this->expectExceptionMessage('Persisted query by that ID doesnt match the provided query; you are likely incorrectly hashing your query.');

        $loader = new CachePersistedQueryLoader($this->cache);

        $loader('asd', OperationParams::create([
            'query' => self::QUERY_STRING
        ]));
    }
}