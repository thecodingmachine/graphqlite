<?php

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use ReflectionClass;

class HardClassFinderComputedCache implements ClassFinderComputedCache
{

    public function __construct(
        private readonly CacheInterface $cache,
    )
    {
    }

    /**
     * @template TEntry of mixed
     * @template TReturn of mixed
     *
     * @param callable(ReflectionClass<object>): TEntry $map
     * @param callable(array<string, TEntry>): TReturn $reduce
     *
     * @return TReturn
     */
    public function compute(
        ClassFinder $classFinder,
        string $key,
        callable $map,
        callable $reduce,
    ): mixed
    {
        $result = $this->cache->get($key);

        if ($result !== null) {
            return $result;
        }

        $result = $reduce($this->entries($classFinder, $map));

        $this->cache->set($key, $result);

        return $result;
    }

    /**
     * @template TEntry of mixed
     *
     * @param callable(ReflectionClass<object>): TEntry $map
     *
     * @return array<string, TEntry>
     */
    private function entries(
        ClassFinder $classFinder,
        callable $map,
    ): mixed
    {
        $entries = [];

        foreach ($classFinder as $classReflection) {
            $entries[$classReflection->getFileName()] = $map($classReflection);
        }

        return $entries;
    }
}