<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use Psr\SimpleCache\CacheInterface;

class ClassBoundCacheContractFactory implements ClassBoundCacheContractFactoryInterface
{
    public function make(CacheInterface $classBoundCache, string $cachePrefix = ''): ClassBoundCacheContractInterface
    {
        return new ClassBoundCacheContract($classBoundCache, $cachePrefix);
    }
}
