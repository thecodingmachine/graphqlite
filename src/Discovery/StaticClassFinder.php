<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery;

use ReflectionClass;
use Traversable;

class StaticClassFinder implements ClassFinder
{
    /** @var (callable(string): bool)|null  */
    private mixed $pathFilter = null;

    /** @param list<class-string> $classes */
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

    /** @return Traversable<class-string, ReflectionClass> */
    public function getIterator(): Traversable
    {
        foreach ($this->classes as $class) {
            $classReflection = new ReflectionClass($class);

            /** @phpstan-ignore-next-line */
            if ($this->pathFilter && ! ($this->pathFilter)($classReflection->getFileName())) {
                continue;
            }

            yield $class => $classReflection;
        }
    }
}
