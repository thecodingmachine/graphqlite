<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;

/**
 * Cache that computes a final value based on class that exist in the application found with
 * the {@see ClassFinder}, and one that allows invalidating only parts of the cache when those
 * classes change, instead of having to invalidate the whole cache on every change.
 */
interface ClassFinderComputedCache
{
    /**
     * Compute the value of the cache. The $finder and $key are self-explanatory; the $map and $reduce need
     * a bit of an explanation: $map is called with each reflection found by $finder, and expects any value to be returned.
     * It will then be stored in a Map<string (filename), TEntry (return from $map)>. Once all classes are iterated,
     * $reduce will then be called with that map, and it's final result is returned.
     *
     * Now the point of this is now whenever file A changes, we can automatically remove entries generated for it
     * and simply call $map only for classes from file A, leaving all other entries untouched and not having to
     * waste resources on the rest of them. We then only need to call the cheap $reduce and have the final result :)
     *
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
    ): mixed;
}
