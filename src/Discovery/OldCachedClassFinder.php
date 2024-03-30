<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use Exception;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class OldCachedClassFinder implements ClassFinder
{
    /**
     * @var array<class-string, \ReflectionClass<object>>
     */
    private array|null $classes = null;

    public function __construct(
        private readonly ClassFinder $finder,
        private readonly CacheInterface $cache,
        private readonly int|null $globTTL = null,
    )
    {
    }

    public function getIterator(): \Traversable
    {
        if ($this->classes === null) {
            $cacheKey = 'GraphQLite_NS_';
            try {
                $classes = $this->cache->get($cacheKey);
                if ($classes !== null) {
                    foreach ($classes as $class) {
                        if (
                            ! class_exists($class, false) &&
                            ! interface_exists($class, false) &&
                            ! trait_exists($class, false)
                        ) {
                            // assume the cache is invalid
                            throw new class extends Exception implements CacheException {
                            };
                        }

                        $this->classes[$class] = new ReflectionClass($class);
                    }
                }
            } catch (CacheException | InvalidArgumentException | ReflectionException) {
                $this->classes = null;
            }

            if ($this->classes === null) {
                $this->classes = [];
                /** @var class-string $className */
                /** @var \ReflectionClass<object> $reflector */
                foreach ($this->finder as $className => $reflector) {
                    if (! ($reflector instanceof ReflectionClass)) {
                        continue;
                    }

                    $this->classes[$className] = $reflector;
                }
                try {
                    $this->cache->set($cacheKey, array_keys($this->classes), $this->globTTL);
                } catch (InvalidArgumentException) {
                    // @ignoreException
                }
            }
        }

        return new \ArrayIterator($this->classes);
    }
}