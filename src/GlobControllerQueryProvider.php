<?php


namespace TheCodingMachine\GraphQLite;

use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and detects "Query" and "Mutation" annotations.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobControllerQueryProvider implements QueryProviderInterface
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var int|null
     */
    private $cacheTtl;
    /**
     * @var array<string,string>|null
     */
    private $instancesList;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var AggregateControllerQueryProvider
     */
    private $aggregateControllerQueryProvider;
    /**
     * @var FieldsBuilderFactory
     */
    private $fieldsBuilderFactory;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;
    /**
     * @var bool
     */
    private $recursive;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     * @param FieldsBuilderFactory $fieldsBuilderFactory
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @param ContainerInterface $container The container we will fetch controllers from.
     * @param CacheInterface $cache
     * @param int|null $cacheTtl
     * @param bool $recursive Whether subnamespaces of $namespace must be analyzed.
     */
    public function __construct(string $namespace, FieldsBuilderFactory $fieldsBuilderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, ContainerInterface $container, CacheInterface $cache, ?int $cacheTtl = null, bool $recursive = true)
    {
        $this->namespace = $namespace;
        $this->container = $container;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
        $this->fieldsBuilderFactory = $fieldsBuilderFactory;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->recursive = $recursive;
    }

    private function getAggregateControllerQueryProvider(): AggregateControllerQueryProvider
    {
        if ($this->aggregateControllerQueryProvider === null) {
            $this->aggregateControllerQueryProvider = new AggregateControllerQueryProvider($this->getInstancesList(), $this->fieldsBuilderFactory, $this->recursiveTypeMapper, $this->container);
        }
        return $this->aggregateControllerQueryProvider;
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return string[]
     */
    private function getInstancesList(): array
    {
        if ($this->instancesList === null) {
            $key = 'globQueryProvider_'.str_replace('\\', '_', $this->namespace);
            $this->instancesList = $this->cache->get($key);
            if ($this->instancesList === null) {
                $this->instancesList = $this->buildInstancesList();
                $this->cache->set($key, $this->instancesList, $this->cacheTtl);
            }
        }
        return $this->instancesList;
    }

    /**
     * @return string[]
     */
    private function buildInstancesList(): array
    {
        $explorer = new GlobClassExplorer($this->namespace, $this->cache, $this->cacheTtl, ClassNameMapper::createFromComposerFile(null, null, true), $this->recursive);
        $classes = $explorer->getClasses();
        $instances = [];
        foreach ($classes as $className) {
            if (!\class_exists($className)) {
                continue;
            }
            $refClass = new \ReflectionClass($className);
            if (!$refClass->isInstantiable()) {
                continue;
            }
            if ($this->container->has($className)) {
                $instances[] = $className;
            }
        }
        return $instances;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        return $this->getAggregateControllerQueryProvider()->getQueries();
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        return $this->getAggregateControllerQueryProvider()->getMutations();
    }
}
