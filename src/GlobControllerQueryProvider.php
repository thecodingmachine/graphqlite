<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Contracts\Cache\CacheInterface as CacheContractInterface;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use Webmozart\Assert\Assert;

use function class_exists;
use function interface_exists;
use function str_replace;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and detects "Query" and "Mutation" annotations.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobControllerQueryProvider implements QueryProviderInterface
{
    /** @var string */
    private $namespace;
    /** @var CacheInterface */
    private $cache;
    /** @var int|null */
    private $cacheTtl;
    /** @var array<string,string>|null */
    private $instancesList;
    /** @var ContainerInterface */
    private $container;
    /** @var ClassNameMapper */
    private $classNameMapper;
    /** @var AggregateControllerQueryProvider */
    private $aggregateControllerQueryProvider;
    /** @var FieldsBuilder */
    private $fieldsBuilder;
    /** @var bool */
    private $recursive;
    /** @var CacheContractInterface */
    private $cacheContract;
    /** @var AnnotationReader */
    private $annotationReader;

    /**
     * @param string             $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     * @param ContainerInterface $container The container we will fetch controllers from.
     * @param bool               $recursive Whether subnamespaces of $namespace must be analyzed.
     */
    public function __construct(string $namespace, FieldsBuilder $fieldsBuilder, ContainerInterface $container, AnnotationReader $annotationReader, CacheInterface $cache, ?ClassNameMapper $classNameMapper = null, ?int $cacheTtl = null, bool $recursive = true)
    {
        $this->namespace       = $namespace;
        $this->container       = $container;
        $this->classNameMapper = $classNameMapper ?? ClassNameMapper::createFromComposerFile(null, null, true);
        $this->cache           = $cache;
        $this->cacheContract   = new Psr16Adapter($this->cache, str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $namespace), $cacheTtl ?? 0);
        $this->cacheTtl        = $cacheTtl;
        $this->fieldsBuilder   = $fieldsBuilder;
        $this->recursive       = $recursive;
        $this->annotationReader = $annotationReader;
    }

    private function getAggregateControllerQueryProvider(): AggregateControllerQueryProvider
    {
        if ($this->aggregateControllerQueryProvider === null) {
            $this->aggregateControllerQueryProvider = new AggregateControllerQueryProvider($this->getInstancesList(), $this->fieldsBuilder, $this->container);
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
            $this->instancesList = $this->cacheContract->get('globQueryProvider', function () {
                return $this->buildInstancesList();
            });
            Assert::isArray($this->instancesList, 'The instance list returned is not an array. There might be an issue with your PSR-16 cache implementation.');
        }

        return $this->instancesList;
    }

    /**
     * @return string[]
     */
    private function buildInstancesList(): array
    {
        $explorer  = new GlobClassExplorer($this->namespace, $this->cache, $this->cacheTtl, $this->classNameMapper, $this->recursive);
        $classes   = $explorer->getClasses();
        $instances = [];
        foreach ($classes as $className) {
            if (! class_exists($className) && ! interface_exists($className)) {
                continue;
            }
            $refClass = new ReflectionClass($className);
            if (! $refClass->isInstantiable()) {
                continue;
            }
            if (! $this->hasQueriesOrMutations($refClass)) {
                continue;
            }
            if (! $this->container->has($className)) {
                continue;
            }

            $instances[] = $className;
        }

        return $instances;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    private function hasQueriesOrMutations(ReflectionClass $reflectionClass): bool
    {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
            $queryAnnotation = $this->annotationReader->getRequestAnnotation($refMethod, Query::class);
            if ($queryAnnotation !== null) {
                return true;
            }
            $mutationAnnotation = $this->annotationReader->getRequestAnnotation($refMethod, Mutation::class);
            if ($mutationAnnotation !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return FieldDefinition[]
     */
    public function getQueries(): array
    {
        return $this->getAggregateControllerQueryProvider()->getQueries();
    }

    /**
     * @return FieldDefinition[]
     */
    public function getMutations(): array
    {
        return $this->getAggregateControllerQueryProvider()->getMutations();
    }
}
