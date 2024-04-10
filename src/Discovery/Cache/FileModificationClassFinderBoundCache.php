<?php

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use ReflectionClass;
use function Safe\filemtime;

/**
 * Provides cache for a {@see ClassFinder} based on a {@see filemtime()}.
 *
 * For example, if you want to "scan" the codebase using class finder to find all enums, you may simply
 * iterate over the class finder and see if there any classes that return `true` from `enum_exists()` check.
 * In production, you can simply cache the result of this operation and on subsequent calls you won't
 * have to iterate over all the classes again; you'll have a complete list of enums already in cache.
 *
 * However, in a development environment you'll usually only change a couple of classes at a time. So you have two options:
 *   1. remove the cache manually or wait for it's expiration
 *   2. not use a cache at all
 *
 * Both options are suboptimal, so to make the developer experience better, this class exists. Basically it does this:
 *   - if no cache exists, it iterates over the whole class finder and returns all reflection that match the filter
 *   - if cache does exist, it only iterates over changed classes
 */
class FileModificationClassFinderBoundCache implements ClassFinderBoundCache
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
    public function reduce(
        ClassFinder $classFinder,
        string $key,
        callable $map,
        callable $reduce,
    ): mixed
    {
        $entries = $this->entries($classFinder, "$key.entries", $map);

        return $reduce($entries);
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
        string $key,
        callable $map,
    ): mixed
    {
        $previousEntries = $this->cache->get($key) ?? [];
        $result = [];
        $entries = [];

        $classFinder = $classFinder->withPathFilter(function (string $filename) use (&$entries, &$result, $previousEntries) {
            $entry = $previousEntries[$filename] ?? null;

            // If there's no entry in cache for this filename (new file or previously uncached),
            // or if it the file has been modified since caching, we'll try to autoload
            // the class and collect the cached information (again).
            if (!$entry || $this->dependenciesChanged($entry['dependencies'])) {
                // In case this file isn't a class, or doesn't match the provided namespace filter,
                // it will not be emitted in the iterator and won't reach the `foreach()` below.
                // So to avoid iterating over these files again, we'll mark them as non-matching.
                // If they are matching, it'll be overwritten in the `foreach` loop below.
                $entries[$filename] = [
                    'dependencies' => [$filename => filemtime($filename)],
                    'matching' => false,
                ];

                return true;
            }

            if ($entry['matching']) {
                $result[$filename] = $entry['data'];
            }

            $entries[$filename] = $entry;

            return false;
        });

        foreach ($classFinder as $classReflection) {
            $filename = $classReflection->getFileName();

            $result[$filename] = $map($classReflection);
            $entries[$filename] = [
                'dependencies' => $this->fileDependencies($classReflection),
                'data' => $result[$filename],
                'matching' => true,
            ];
        }

        $this->cache->set($key, $entries);

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function fileDependencies(ReflectionClass $refClass): array
    {
        $filename = $refClass->getFileName();

        if ($filename === false) {
            return [];
        }

        $files = [$filename => filemtime($filename)];

        if ($refClass->getParentClass() !== false) {
            $files = array_merge($files, $this->fileDependencies($refClass->getParentClass()));
        }

        foreach ($refClass->getTraits() as $trait) {
            $files = array_merge($files, $this->fileDependencies($trait));
        }

        foreach ($refClass->getInterfaces() as $interface) {
            $files = array_merge($files, $this->fileDependencies($interface));
        }

        return $files;
    }

    private function dependenciesChanged(array $files): bool
    {
        foreach ($files as $filename => $modificationTime) {
            if ($modificationTime !== filemtime($filename)) {
                return true;
            }
        }

        return false;
    }
}