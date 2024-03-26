<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils\Namespaces;

use Kcs\ClassFinder\Finder\FinderInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Factory class in charge of creating NS instances
 *
 * @internal
 */
final class NamespaceFactory
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly FinderInterface $finder,
        private int|null $globTTL = 2,
    ) {
    }

    /** @param string $namespace A PHP namespace */
    public function createNamespace(string $namespace): NS
    {
        return new NS($namespace, $this->cache, clone $this->finder, $this->globTTL);
    }
}
