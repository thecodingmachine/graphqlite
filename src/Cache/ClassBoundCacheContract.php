<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;

use function str_replace;

class ClassBoundCacheContract implements ClassBoundCacheContractInterface
{
    private readonly string $cachePrefix;

    public function __construct(private readonly CacheInterface $classBoundCache, string $cachePrefix = '')
    {
        $this->cachePrefix = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $cachePrefix);
    }

    /**
     * @param string $key An optional key to differentiate between cache items attached to the same class.
     *
     * @throws InvalidArgumentException
     */
    public function get(ReflectionClass $reflectionClass, callable $resolver, string $key = '', int|null $ttl = null): mixed
    {
        $cacheKey = $reflectionClass->getName() . '__' . $key;
        $cacheKey = $this->cachePrefix . str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $cacheKey);

        $item = $this->classBoundCache->get($cacheKey);
        if ($item !== null) {
            return $item;
        }

        $item = $resolver();

        $this->classBoundCache->set($cacheKey, $item, $ttl);

        return $item;
    }
}
