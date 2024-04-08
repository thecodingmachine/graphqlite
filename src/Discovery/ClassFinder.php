<?php

namespace TheCodingMachine\GraphQLite\Discovery;

/**
 * @extends \IteratorAggregate<class-string, \ReflectionClass<object>>
 */
interface ClassFinder extends \IteratorAggregate
{
    public function withPathFilter(callable $filter): self;
}