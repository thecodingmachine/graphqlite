<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils\Namespaces;

use Kcs\ClassFinder\Finder\FinderInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;

/**
 * The NS class represents a PHP Namespace and provides utility methods to explore those classes.
 *
 * @internal
 */
final class NS
{
    /**
     * The array of globbed classes.
     * Only instantiable classes are returned.
     * Key: fully qualified class name
     *
     * @var array<class-string,ReflectionClass<object>>
     */
    private array|null $classes = null;

    /** @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation) */
    public function __construct(
        private readonly string $namespace,
        private readonly CacheInterface $cache,
        private readonly FinderInterface $finder,
        private readonly int|null $globTTL,
    ) {
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<class-string,ReflectionClass<object>> Key: fully qualified class name
     */
    public function getClassList(): array
    {
        if ($this->classes === null) {
            $cacheKey = 'GraphQLite_NS_' . $this->namespace;
            try {
                $this->classes = $this->cache->get($cacheKey);
            } catch (InvalidArgumentException) {
                $this->classes = null;
            }

            if ($this->classes === null) {
                $this->classes = [];
                /** @var class-string $className */
                /** @var ReflectionClass<object> $reflector */
                foreach ($this->finder->inNamespace($this->namespace) as $className => $reflector) {
                    if (! ($reflector instanceof ReflectionClass)) {
                        continue;
                    }

                    $this->classes[$className] = $reflector;
                }
                try {
                    $this->cache->set($cacheKey, $this->classes, $this->globTTL);
                } catch (InvalidArgumentException) {
                    // @ignoreException
                }
            }
        }

        return $this->classes;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
