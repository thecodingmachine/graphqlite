<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use DateInterval;
use PackageVersions\Versions;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function md5;
use function substr;

/**
 * A cache adapter that adds a namespace depending on the package version.
 * On each new version, the cache is automatically purged.
 */
class NamespacedCache implements CacheInterface
{
    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $namespace;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->namespace = substr(md5(Versions::getVersion('thecodingmachine/graphqlite')), 0, 8);
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        return $this->cache->get($this->namespace . $key, $default);
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param int|DateInterval|null $ttl Optional. The TTL value of this item. If no value is sent and
     * the driver supports TTL then the library may set a default value
     * for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->cache->set($this->namespace . $key, $value, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key): bool
    {
        return $this->cache->delete($this->namespace . $key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string, mixed> $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable,
     * or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $values = $this->cache->getMultiple($this->namespacedKeys($keys), $default);
        $shortenedKeys = [];
        foreach ($values as $key => $value) {
            $shortenedKeys[substr($key, 8)] = $value;
        }

        return $shortenedKeys;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable<string, mixed> $values A list of key => value pairs for a multiple-set operation.
     * @param int|DateInterval|null $ttl Optional. The TTL value of this item. If no value is sent and
     * the driver supports TTL then the library may set a default value
     * for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException MUST be thrown if $values is neither an array nor a Traversable,
     * or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $namespacedValues = [];
        foreach ($values as $key => $value) {
            $namespacedValues[$this->namespace . $key] = $value;
        }

        return $this->cache->setMultiple($namespacedValues, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<int, string> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable,
     * or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys): bool
    {
        return $this->cache->deleteMultiple($this->namespacedKeys($keys));
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     */
    public function has($key): bool
    {
        return $this->cache->has($this->namespace . $key);
    }

    /**
     * @param iterable<int, string> $keys
     *
     * @return string[]
     */
    private function namespacedKeys($keys): array
    {
        $namespacedKeys = [];
        foreach ($keys as $key) {
            $namespacedKeys[] = $this->namespace . $key;
        }

        return $namespacedKeys;
    }
}
