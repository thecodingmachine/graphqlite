<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use DateInterval;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;
use Psr\SimpleCache\CacheInterface;

/**
 * Uses cache to automatically store persisted queries, a.k.a. Apollo automatic persisted queries.
 *
 * @phpstan-import-type PersistedQueryLoader from ServerConfig
 *
 * @implements PersistedQueryLoader
 */
class CachePersistedQueryLoader
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly DateInterval|null $ttl = null,
    ) {}

    public function __invoke(string $queryId, OperationParams $operation): string|DocumentNode
    {
        $queryId = mb_strtolower($queryId);

        if ($query = $this->cache->get($queryId)) {
            return $query;
        }

        $query = $operation->query;

        if (!$query) {
            throw new PersistedQueryNotFoundException();
        }

        if (!$this->queryMatchesId($queryId, $query)) {
            throw new PersistedQueryIdInvalidException();
        }

        $this->cache->set($queryId, $query, $this->ttl);

        return $query;
    }

    private function queryMatchesId(string $queryId, string $query): bool
    {
        return $queryId === hash('sha256', $query);
    }
}