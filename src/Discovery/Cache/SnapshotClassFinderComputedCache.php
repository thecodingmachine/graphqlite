<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;

use function sprintf;
use function str_replace;

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
class SnapshotClassFinderComputedCache implements ClassFinderComputedCache
{
    public function __construct(
        private readonly CacheInterface $cache,
    )
    {
    }

    /**
     * @param callable(ReflectionClass<object>): TEntry $map
     * @param callable(array<string, TEntry>): TReturn $reduce
     *
     * @return TReturn
     *
     * @template TEntry of mixed
     * @template TReturn of mixed
     */
    public function compute(
        ClassFinder $classFinder,
        string $key,
        callable $map,
        callable $reduce,
    ): mixed
    {
        $key = sprintf('%s.%s', $key, $classFinder->hash());
        $entries = $this->entries($classFinder, $key . '.entries', $map);

        return $reduce($entries);
    }

    /**
     * @param callable(ReflectionClass<object>): TEntry $map
     *
     * @return array<string, TEntry>
     *
     * @template TEntry of mixed
     */
    private function entries(
        ClassFinder $classFinder,
        string $key,
        callable $map,
    ): mixed
    {
        $previousEntries = $this->cache->get($key) ?? [];
        /** @var array<string, TEntry> $result */
        $result = [];
        $entries = [];

        // The size of the cache may be huge, so let's avoid writes when unnecessary.
        $changed = false;

        $classFinder = $classFinder->withPathFilter(static function (string $filename) use (&$entries, &$result, &$changed, $previousEntries) {
            // Normalize filename to avoid issues on Windows.
            $normalizedFilename = str_replace('\\', '/', $filename);

            /** @var array{ data: TEntry, dependencies: FilesSnapshot, matching: bool } $entry */
            $entry = $previousEntries[$normalizedFilename] ?? null;

            // If there's no entry in cache for this filename (new file or previously uncached),
            // or if it the file has been modified since caching, we'll try to autoload
            // the class and collect the cached information (again).
            if (! $entry || $entry['dependencies']->changed()) {
                // In case this file isn't a class, or doesn't match the provided namespace filter,
                // it will not be emitted in the iterator and won't reach the `foreach()` below.
                // So to avoid iterating over these files again, we'll mark them as non-matching.
                // If they are matching, it'll be overwritten in the `foreach` loop below.
                $entries[$normalizedFilename] = [
                    'dependencies' => FilesSnapshot::for([$filename]),
                    'matching' => false,
                ];

                $changed = true;

                return true;
            }

            if ($entry['matching']) {
                $result[$normalizedFilename] = $entry['data'];
            }

            $entries[$normalizedFilename] = $entry;

            return false;
        });

        foreach ($classFinder as $classReflection) {
            $filename = $classReflection->getFileName();

            // Skip internal classes or classes without a file
            if ($filename === false) {
                continue;
            }

            // Normalize filename to avoid issues on Windows.
            $normalizedFilename = str_replace('\\', '/', $filename);

            $result[$normalizedFilename] = $map($classReflection);
            $entries[$normalizedFilename] = [
                'dependencies' => FilesSnapshot::forClass($classReflection, true),
                'data' => $result[$normalizedFilename],
                'matching' => true,
            ];

            $changed = true;
        }

        if ($changed) {
            $this->cache->set($key, $entries);
        }

        /** @phpstan-ignore return.type */
        return $result;
    }
}
