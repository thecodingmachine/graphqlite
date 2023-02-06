<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils\Namespaces;

use Mouf\Composer\ClassNameMapper;
use Psr\SimpleCache\CacheInterface;

/**
 * Factory class in charge of creating NS instances
 *
 * @internal
 */
final class NamespaceFactory
{
    private ClassNameMapper $classNameMapper;

    public function __construct(private readonly CacheInterface $cache, ClassNameMapper|null $classNameMapper = null, private int|null $globTTL = 2)
    {
        $this->classNameMapper = $classNameMapper ?? ClassNameMapper::createFromComposerFile(null, null, true);
    }

    /** @param string $namespace A PHP namespace */
    public function createNamespace(string $namespace, bool $recursive = true): NS
    {
        return new NS($namespace, $this->cache, $this->classNameMapper, $this->globTTL, $recursive);
    }
}
