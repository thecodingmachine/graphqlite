<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils\Namespaces;

use Mouf\Composer\ClassNameMapper;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;

use function class_exists;
use function interface_exists;

/**
 * The NS class represents a PHP Namespace and provides utility methods to explore those classes.
 *
 * @internal
 */
final class NS
{
    /** @var string */
    private $namespace;
    /**
     * The array of globbed classes.
     * Only instantiable classes are returned.
     * Key: fully qualified class name
     *
     * @var array<string,ReflectionClass<object>>
     */
    private $classes;
    /** @var bool */
    private $recursive;
    /** @var ClassNameMapper */
    private $classNameMapper;
    /** @var CacheInterface */
    private $cache;
    /** @var int|null */
    private $globTTL;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(string $namespace, CacheInterface $cache, ClassNameMapper $classNameMapper, ?int $globTTL, bool $recursive)
    {
        $this->namespace           = $namespace;
        $this->recursive           = $recursive;
        $this->cache = $cache;
        $this->classNameMapper = $classNameMapper;
        $this->globTTL = $globTTL;
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass<object>> Key: fully qualified class name
     */
    public function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            $explorer      = new GlobClassExplorer($this->namespace, $this->cache, $this->globTTL, $this->classNameMapper, $this->recursive);
            $classes       = $explorer->getClassMap();
            foreach ($classes as $className => $phpFile) {
                if (! class_exists($className, false) && ! interface_exists($className, false)) {
                    // Let's try to load the file if it was not imported yet.
                    // We are importing the file manually to avoid triggering the autoloader.
                    // The autoloader might trigger errors if the file does not respect PSR-4 or if the
                    // Symfony DebugAutoLoader is installed. (see https://github.com/thecodingmachine/graphqlite/issues/216)
                    require_once $phpFile;
                    // Does it exists now?
                    // @phpstan-ignore-next-line
                    if (! class_exists($className, false) && ! interface_exists($className, false)) {
                        continue;
                    }
                }

                $refClass = new ReflectionClass($className);
                // Enum's are not classes
                if (interface_exists(\UnitEnum::class)) {
                    // @phpstan-ignore-next-line - Remove this after minimum supported PHP version is >= 8.1
                    if ($refClass->isEnum()) {
                        continue;
                    }
                }

                $this->classes[$className] = $refClass;
            }
        }

        return $this->classes;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
