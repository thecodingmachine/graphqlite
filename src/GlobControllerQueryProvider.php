<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Subscription;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;

use function array_filter;
use function array_values;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and detects "Query", "Mutation", and "Subscription" annotations.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobControllerQueryProvider implements QueryProviderInterface
{
    /** @var array<int,class-string> */
    private array $classList;
    private AggregateControllerQueryProvider|null $aggregateControllerQueryProvider = null;

    /** @param ContainerInterface $container The container we will fetch controllers from. */
    public function __construct(
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly ContainerInterface $container,
        private readonly AnnotationReader $annotationReader,
        private readonly ClassFinder $classFinder,
        private readonly ClassFinderComputedCache $classFinderComputedCache,
    )
    {
    }

    private function getAggregateControllerQueryProvider(): AggregateControllerQueryProvider
    {
        $this->aggregateControllerQueryProvider ??= new AggregateControllerQueryProvider(
            $this->getClassList(),
            $this->fieldsBuilder,
            $this->container,
        );

        return $this->aggregateControllerQueryProvider;
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return array<int,class-string>
     */
    private function getClassList(): array
    {
        /** @phpstan-ignore assign.propertyType */
        $this->classList ??= $this->classFinderComputedCache->compute(
            $this->classFinder,
            'globQueryProvider',
            function (ReflectionClass $classReflection): string|null {
                if (
                    ! $classReflection->isInstantiable() ||
                    ! $this->hasOperations($classReflection) ||
                    ! $this->container->has($classReflection->getName())
                ) {
                    return null;
                }

                return $classReflection->getName();
            },
            static fn (array $entries) => array_values(array_filter($entries)),
        );

        /** @phpstan-ignore return.type */
        return $this->classList;
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
