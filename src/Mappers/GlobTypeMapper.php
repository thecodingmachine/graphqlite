<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;
use function class_exists;
use function interface_exists;
use function str_replace;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and uses the @Type annotation to find the types automatically.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobTypeMapper extends AbstractTypeMapper
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

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(string $namespace, TypeGenerator $typeGenerator, InputTypeGenerator $inputTypeGenerator, InputTypeUtils $inputTypeUtils, ContainerInterface $container, AnnotationReader $annotationReader, NamingStrategyInterface $namingStrategy, RecursiveTypeMapperInterface $recursiveTypeMapper, CacheInterface $cache, ?ClassNameMapper $classNameMapper = null, ?int $globTtl = 2, ?int $mapTtl = null, bool $recursive = true)
    {
        $this->namespace           = $namespace;
        $this->recursive           = $recursive;
        $this->classNameMapper     = $classNameMapper ?? ClassNameMapper::createFromComposerFile(null, null, true);
        $cachePrefix = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $namespace);
        parent::__construct($cachePrefix, $typeGenerator, $inputTypeGenerator, $inputTypeUtils, $container, $annotationReader, $namingStrategy, $recursiveTypeMapper, $cache, $globTtl, $mapTtl);
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass<object>> Key: fully qualified class name
     */
    protected function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            $explorer      = new GlobClassExplorer($this->namespace, $this->cache, $this->globTtl, $this->classNameMapper, $this->recursive);
            $classes       = $explorer->getClassMap();
            foreach ($classes as $className => $fileInfo) {
                if (! class_exists($className, false) && ! interface_exists($className, false)) {
                    // Let's try to load the file if it was not imported yet.
                    // We are importing the file manually to avoid triggering the autoloader.
                    // The autoloader might trigger errors if the file does not respect PSR-4 or if the
                    // Symfony DebugAutoLoader is installed. (see https://github.com/thecodingmachine/graphqlite/issues/216)
                    require_once $fileInfo->getRealPath();
                    // Does it exists now?
                    if (! class_exists($className, false) && ! interface_exists($className, false)) {
                        continue;
                    }
                }

                $refClass = new ReflectionClass($className);
                $this->classes[$className] = $refClass;
            }
        }

        return $this->classes;
    }
}
