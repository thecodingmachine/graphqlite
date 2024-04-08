<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use Traversable;

class StaticClassFinder implements ClassFinder
{
    /** @var (callable(string): bool)|null  */
    private mixed $pathFilter = null;

    /**
     * @param array<int, class-string> $classes
     */
    public function __construct(
        private readonly array $classes,
    )
    {
    }

    public function withPathFilter(callable $filter): ClassFinder
    {
        $that = clone $this;
        $that->pathFilter = $filter;

        return $that;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->classes as $class) {
            $classReflection = new \ReflectionClass($class);

            if ($this->pathFilter && !($this->pathFilter)($classReflection->getFileName())) {
                continue;
            }

            yield $class => $classReflection;
        }
    }
}