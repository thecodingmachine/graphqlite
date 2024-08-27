<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

use function str_replace;

class FileModificationClassBoundCache implements ClassBoundCache
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public function get(ReflectionClass $reflectionClass, callable $resolver, string $key = '', bool $useInheritance = false): mixed
    {
        $cacheKey = $reflectionClass->getName() . '__' . $key;
        $cacheKey = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $cacheKey);

        $item = $this->cache->get($cacheKey);

        if ($item !== null && ! $item['snapshot']->changed()) {
            return $item['data'];
        }

        $item = [
            'data' => $resolver(),
            'snapshot' => ClassSnapshot::fromReflection($reflectionClass),
        ];

        $this->cache->set($cacheKey, $item);

        return $item['data'];
    }
}
