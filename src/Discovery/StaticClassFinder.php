<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Discovery;

use ReflectionClass;
use Traversable;

use function md5;
use function serialize;

class StaticClassFinder implements ClassFinder
{
    /** @var (callable(string): bool)|null  */
    private mixed $pathFilter = null;

    private string|null $hash = null;

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

    public function hash(): string
    {
        return $this->hash ??= md5(serialize($this->classes));
    }
}
