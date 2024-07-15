<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use Psr\SimpleCache\CacheInterface;

interface ClassBoundCacheContractFactoryInterface
{
    public function make(CacheInterface $classBoundCache, string $cachePrefix = ''): ClassBoundCacheContractInterface;
}
