<?php

namespace TheCodingMachine\GraphQLite\Discovery\Cache;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;

interface ClassFinderBoundCache
{
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
    ): mixed;
}