<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use InvalidArgumentException;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Finder\FinderInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Contracts\Cache\CacheInterface as CacheContractInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Subscription;

use function class_exists;
use function interface_exists;
use function is_array;
use function str_replace;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and detects "Query", "Mutation", and "Subscription" annotations.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobControllerQueryProvider implements QueryProviderInterface
{
    /** @var array<int,string>|null */
    private array|null $instancesList = null;
    private FinderInterface $finder;
    private AggregateControllerQueryProvider|null $aggregateControllerQueryProvider = null;
    private CacheContractInterface $cacheContract;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     * @param ContainerInterface $container The container we will fetch controllers from.
     */
    public function __construct(
        private readonly string $namespace,
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly ContainerInterface $container,
        private readonly AnnotationReader $annotationReader,
        private readonly CacheInterface $cache,
        FinderInterface|null $finder = null,
        int|null $cacheTtl = null,
    )
    {
        $this->finder = $finder ?? new ComposerFinder();
        $this->cacheContract = new Psr16Adapter(
            $this->cache,
            str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', $namespace),
            $cacheTtl ?? 0,
        );
    }

    private function getAggregateControllerQueryProvider(): AggregateControllerQueryProvider
    {
        $this->aggregateControllerQueryProvider ??= new AggregateControllerQueryProvider(
            $this->getInstancesList(),
            $this->fieldsBuilder,
            $this->container,
        );

        return $this->aggregateControllerQueryProvider;
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return array<int,string>
     */
    private function getInstancesList(): array
    {
        if ($this->instancesList === null) {
            $this->instancesList = $this->cacheContract->get(
                'globQueryProvider',
                fn () => $this->buildInstancesList(),
            );

            if (! is_array($this->instancesList)) {
                throw new InvalidArgumentException('The instance list returned is not an array. There might be an issue with your PSR-16 cache implementation.');
            }
        }

        return $this->instancesList;
    }

    /** @return array<int,string> */
    private function buildInstancesList(): array
    {
        $instances = [];
        foreach ((clone $this->finder)->inNamespace($this->namespace) as $className => $refClass) {
            if (! class_exists($className) && ! interface_exists($className)) {
                continue;
            }
            if (! $refClass instanceof ReflectionClass || ! $refClass->isInstantiable()) {
                continue;
            }
            if (! $this->hasOperations($refClass)) {
                continue;
            }
            if (! $this->container->has($className)) {
                continue;
            }

            $instances[] = $className;
        }

        return $instances;
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function hasOperations(ReflectionClass $reflectionClass): bool
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
            $subscriptionAnnotation = $this->annotationReader->getRequestAnnotation($refMethod, Subscription::class);
            if ($subscriptionAnnotation !== null) {
                return true;
            }
        }
        return false;
    }

    /** @return array<string,FieldDefinition> */
    public function getQueries(): array
    {
        return $this->getAggregateControllerQueryProvider()->getQueries();
    }

    /** @return array<string,FieldDefinition> */
    public function getMutations(): array
    {
        return $this->getAggregateControllerQueryProvider()->getMutations();
    }

    /** @return array<string,FieldDefinition> */
    public function getSubscriptions(): array
    {
        return $this->getAggregateControllerQueryProvider()->getSubscriptions();
    }
}
