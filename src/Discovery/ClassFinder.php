<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery;

use IteratorAggregate;
use ReflectionClass;

/** @extends IteratorAggregate<class-string, ReflectionClass<object>> */
interface ClassFinder extends IteratorAggregate
{
    public function withPathFilter(callable $filter): self;
}
