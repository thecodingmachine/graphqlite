<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

use function str_replace;

class SnapshotClassBoundCache implements ClassBoundCache
{
    /**
     * @param callable(ReflectionClass, bool $withInheritance): FilesSnapshot $filesSnapshotFactory
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly mixed $filesSnapshotFactory,
    ) {
    }

    public function get(ReflectionClass $reflectionClass, callable $resolver, string $key = '', bool $withInheritance = false): mixed
    {
        $cacheKey = $reflectionClass->getName() . '__' . $key;
        $cacheKey = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $cacheKey);

        $item = $this->cache->get($cacheKey);

        if ($item !== null && ! $item['snapshot']->changed()) {
            return $item['data'];
        }

        $item = [
            'data' => $resolver(),
            'snapshot' => ($this->filesSnapshotFactory)($reflectionClass, $withInheritance),
        ];

        $this->cache->set($cacheKey, $item);

        return $item['data'];
    }
}
