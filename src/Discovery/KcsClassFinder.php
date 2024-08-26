<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery;

use Kcs\ClassFinder\Finder\FinderInterface;
use ReflectionClass;
use Traversable;

class KcsClassFinder implements ClassFinder
{
    public function __construct(
        private FinderInterface $finder,
    )
    {
    }

    public function withPathFilter(callable $filter): ClassFinder
    {
        $that = clone $this;
        $that->finder = (clone $that->finder)->pathFilter($filter);

        return $that;
    }

    /** @return Traversable<class-string, ReflectionClass> */
    public function getIterator(): Traversable
    {
        return $this->finder->getIterator();
    }
}
