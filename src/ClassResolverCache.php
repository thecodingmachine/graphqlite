<?php


namespace TheCodingMachine\GraphQLite;

use Psr\SimpleCache\CacheInterface;

class ClassResolverCache implements ClassResolver
{
    /** @var CacheInterface */
    private $cache;
    /** @var ClassResolver */
    private $classResolver;

    public function __construct(CacheInterface $cache, ClassResolver $classResolver)
    {
        $this->cache = $cache;
        $this->classResolver = $classResolver;
    }

    public function __invoke(array $classList): iterable
    {
        $key = 'classresolver_' . md5(json_encode($classList));

        $cacheItem = $this->cache->get($key);

        if ($cacheItem !== null) {
            return $cacheItem;
        }

        $result = $this->classResolver->__invoke($classList);

        $this->cache->set($key, $result);

        return $result;
    }
}