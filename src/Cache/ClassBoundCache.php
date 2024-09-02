<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use ReflectionClass;

interface ClassBoundCache
{
    /**
     * @param callable(): TReturn $resolver
     *
     * @return TReturn
     *
     * @template TReturn
     */
    public function get(
        ReflectionClass $reflectionClass,
        callable        $resolver,
        string          $key,
        bool            $withInheritance = false,
    ): mixed;
}
