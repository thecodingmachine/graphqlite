<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NS;

/**
 * A context class containing a number of classes created on the fly by SchemaFactory.
 * Those classes are made available to factories implementing RootTypeMapperFactoryInterface
 */
final class RootTypeMapperFactoryContext
{
    /**
     * @param iterable<NS> $typeNamespaces
     */
    public function __construct(
        private readonly AnnotationReader $annotationReader,
        private readonly TypeResolver $typeResolver,
        private readonly NamingStrategyInterface $namingStrategy,
        private readonly TypeRegistry $typeRegistry,
        private readonly RecursiveTypeMapperInterface $recursiveTypeMapper,
        private readonly ContainerInterface $container,
        private readonly CacheInterface $cache,
        private readonly iterable $typeNamespaces,
        private readonly int|null $globTTL,
        private readonly int|null $mapTTL = null,
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

    /** @return iterable<NS> */
    public function getTypeNamespaces(): iterable
    {
        return $this->typeNamespaces;
    }

    public function getGlobTTL(): int|null
    {
        return $this->globTTL;
    }

    public function getMapTTL(): int|null
    {
        return $this->mapTTL;
    }
}
