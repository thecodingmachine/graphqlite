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
    /**
     * The array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @var array<class-string, ReflectionClass<object>>
     */
    private array|null $classes = null;

    /** @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation) */
    public function __construct(
        private readonly string $namespace,
        private readonly CacheInterface $cache,
        private readonly ClassNameMapper $classNameMapper,
        private readonly int|null $globTTL,
        private readonly bool $recursive,
        private readonly bool $autoload = true,
    ) {
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<class-string, ReflectionClass<object>> Key: fully qualified class name
     */
    public function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            $explorer = new GlobClassExplorer($this->namespace, $this->cache, $this->globTTL, $this->classNameMapper, $this->recursive);
            /** @var array<class-string, string> $classes Override class-explorer lib */
            $classes = $explorer->getClassMap();
            foreach ($classes as $className => $phpFile) {
                if (!$this->loadClass($className, $phpFile)) {
                    continue;
                }

                $this->classes[$className] = new ReflectionClass($className);
            }
        }

        return $this->classes;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Attempt to load a class depending on the @see $autoload setting.
     *
     * @param class-string $className
     */
    private function loadClass(string $className, string $phpFile): bool
    {
        if (class_exists($className, $this->autoload) || interface_exists($className, $this->autoload)) {
            return true;
        }

        // If autoloading was requested and there's no class by this name, then it's most likely that the
        // guessed class name from GlobClassExplorer is simply not a class, so we'll skip it.
        // See: https://github.com/thecodingmachine/graphqlite/issues/659
        if ($this->autoload) {
            return false;
        }

        // Otherwise attempt to load the file without autoloading.
        // The autoloader might trigger errors if the file does not respect PSR-4 or if the
        // Symfony DebugAutoLoader is installed. (see https://github.com/thecodingmachine/graphqlite/issues/216)
        require_once $phpFile;

        // The class might still not be loaded if guessed class name doesn't match the PHP file,
        // so we should check if it got loaded after requiring the PHP file.
        return class_exists($className, false) || interface_exists($className, false);
    }
}
