<?php


namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\Reader;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQL\Controllers\AggregateControllerQueryProvider;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\AnnotationUtils;
use TheCodingMachine\GraphQL\Controllers\QueryField;
use TheCodingMachine\GraphQL\Controllers\QueryProviderInterface;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

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
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     * @param ContainerInterface|null $container The container we will fetch controllers from. If not specified, container from the registry is used instead.
     */
    public function __construct(string $namespace, RegistryInterface $registry, ?ContainerInterface $container, CacheInterface $cache, ?int $cacheTtl = null)
    {
        $this->namespace = $namespace;
        $this->registry = $registry;
        $this->container = $container ?: $registry;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    private function getAggregateControllerQueryProvider(): AggregateControllerQueryProvider
    {
        if ($this->aggregateControllerQueryProvider === null) {
            $this->aggregateControllerQueryProvider = new AggregateControllerQueryProvider($this->getInstancesList(), $this->registry, $this->container);
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
        $explorer = new GlobClassExplorer($this->namespace, $this->cache, $this->cacheTtl, ClassNameMapper::createFromComposerFile(null, null, true));
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
