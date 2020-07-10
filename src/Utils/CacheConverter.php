<?php


namespace TheCodingMachine\GraphQLite\Utils;


use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use function get_class;

/**
 * @internal
 */
final class CacheConverter
{
    /**
     * @param CacheItemPoolInterface|CacheInterface $cache
     * @return CacheItemPoolInterface
     */
/*    public static function toPsr6Cache(object $cache, string $namespace = ''): CacheItemPoolInterface
    {
        if ($cache instanceof CacheItemPoolInterface) {
            if ($namespace !== '') {
                return new ProxyAdapter($cache, $namespace);
            }
            return $cache;
        }
        if ($cache instanceof CacheInterface) {
            return new Psr16Adapter($cache, $namespace);
        }
        throw new InvalidArgumentException('Expected a PSR-6 or PSR-16 compatible cache. Got '.get_class($cache));
    }*/

    /**
     * @param CacheItemPoolInterface|CacheInterface|SymfonyCacheInterface $cache
     * @return SymfonyCacheInterface&CacheItemPoolInterface
     */
    public static function toSymfonyCache(object $cache, string $namespace = ''): SymfonyCacheInterface
    {
        if ($cache instanceof SymfonyCacheInterface) {
            if ($namespace !== '') {
                return new ProxyAdapter($cache, $namespace);
            }
            return $cache;
        }
        if ($cache instanceof CacheInterface) {
            return new Psr16Adapter($cache);
        }
        if ($cache instanceof CacheItemPoolInterface) {
            return new ProxyAdapter($cache);
        }
        throw new InvalidArgumentException('Expected a PSR-6 or PSR-16 or Symfony cache contracts compatible cache. Got '.get_class($cache));
    }
}
