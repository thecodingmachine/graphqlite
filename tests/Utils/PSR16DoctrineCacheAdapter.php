<?php


namespace TheCodingMachine\GraphQLite\Utils;


use Doctrine\Common\Cache\CacheProvider;
use Psr\SimpleCache\CacheInterface;

/**
 * An adapter to turn a PSR-16 cache into a Doctrine Cache
 */
class PSR16DoctrineCacheAdapter extends CacheProvider
{
    /**
     * PSR-16 cache.
     *
     * @var CacheInterface
     */
    protected $cache;
    /**
     * PsrDoctrineCacheBridge constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    protected function doFetch($id)
    {
        return $this->cache->get($id, false);
    }
    protected function doContains($id)
    {
        return $this->cache->has($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return $this->cache->set($id, $data, (int) $lifeTime ?: null);
    }
    protected function doDelete($id)
    {
        return $this->cache->delete($id);
    }
    protected function doFlush()
    {
        $this->cache->clear();
    }
    protected function doGetStats()
    {
        // Do nothing
    }
}
