<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\Cache\ClassBoundCache;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\InputTypeValidatorInterface;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

/**
 * A context class containing a number of classes created on the fly by SchemaFactory.
 * Those classes are made available to factories implementing QueryProviderFactoryInterface
 * or TypeMapperFactoryInterface
 */
final class FactoryContext
{
    public function __construct(
        private readonly AnnotationReader $annotationReader,
        private readonly TypeResolver $typeResolver,
        private readonly NamingStrategyInterface $namingStrategy,
        private readonly TypeRegistry $typeRegistry,
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly TypeGenerator $typeGenerator,
        private readonly InputTypeGenerator $inputTypeGenerator,
        private readonly RecursiveTypeMapperInterface $recursiveTypeMapper,
        private readonly ContainerInterface $container,
        private readonly CacheInterface $cache,
        private readonly InputTypeValidatorInterface|null $inputTypeValidator,
        private readonly ClassFinder $classFinder,
        private readonly ClassFinderComputedCache $classFinderComputedCache,
        private readonly ClassBoundCache $classBoundCache,
    ) {
    }

    public function getAnnotationReader(): AnnotationReader
    {
        return $this->annotationReader;
    }

    public function getTypeResolver(): TypeResolver
    {
        return $this->typeResolver;
    }

    public function getNamingStrategy(): NamingStrategyInterface
    {
        return $this->namingStrategy;
    }

    public function getTypeRegistry(): TypeRegistry
    {
        return $this->typeRegistry;
    }

    public function getFieldsBuilder(): FieldsBuilder
    {
        return $this->fieldsBuilder;
    }

    public function getTypeGenerator(): TypeGenerator
    {
        return $this->typeGenerator;
    }

    public function getInputTypeGenerator(): InputTypeGenerator
    {
        return $this->inputTypeGenerator;
    }

    public function getRecursiveTypeMapper(): RecursiveTypeMapperInterface
    {
        return $this->recursiveTypeMapper;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function getInputTypeValidator(): InputTypeValidatorInterface|null
    {
        return $this->inputTypeValidator;
    }

    public function getClassFinder(): ClassFinder
    {
        return $this->classFinder;
    }

    public function getClassFinderComputedCache(): ClassFinderComputedCache
    {
        return $this->classFinderComputedCache;
    }

    public function getClassBoundCache(): ClassBoundCache|null
    {
        return $this->classBoundCache;
    }
}
