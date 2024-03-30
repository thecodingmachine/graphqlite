<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use Traversable;

class EmptyClassFinder implements ClassFinder
{
    public function getIterator(): Traversable
    {
        return new \EmptyIterator();
    }
}