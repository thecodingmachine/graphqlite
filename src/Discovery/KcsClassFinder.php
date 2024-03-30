<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use Kcs\ClassFinder\Finder\FinderInterface;
use Traversable;

class KcsClassFinder implements ClassFinder
{
    public function __construct(
        private readonly FinderInterface $finder,
    )
    {
    }

    public function getIterator(): Traversable
    {
        return $this->finder->getIterator();
    }
}