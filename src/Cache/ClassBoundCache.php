<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use ReflectionClass;

interface ClassBoundCache
{
    /**
     * @param string $key An optional key to differentiate between cache items attached to the same class.
     * @param callable(): TReturn $resolver
     *
     * @return TReturn
     *
     * @template TReturn
     */
    public function get(
        ReflectionClass $reflectionClass,
        callable $resolver,
        string $key = '',
        bool $useInheritance = false,
    ): mixed;
}
