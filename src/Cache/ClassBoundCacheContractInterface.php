<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use ReflectionClass;

interface ClassBoundCacheContractInterface
{
    /** @param string $key An optional key to differentiate between cache items attached to the same class. */
    public function get(ReflectionClass $reflectionClass, callable $resolver, string $key = '', int|null $ttl = null): mixed;
}
