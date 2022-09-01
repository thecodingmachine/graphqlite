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

/**
 * A context class containing a number of classes created on the fly by SchemaFactory.
 * Those classes are made available to factories implementing RootTypeMapperFactoryInterface
 */
final class RootTypeMapperFactoryContext
{
    public function __construct(
        private AnnotationReader $annotationReader,
        private TypeResolver $typeResolver,
        private NamingStrategyInterface $namingStrategy,
        private TypeRegistry $typeRegistry,
        private RecursiveTypeMapperInterface $recursiveTypeMapper,
        private ContainerInterface $container,
        private CacheInterface $cache,
        private ?int $globTTL,
        private ?int $mapTTL = null
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

    public function getGlobTTL(): ?int
    {
        return $this->globTTL;
    }

    public function getMapTTL(): ?int
    {
        return $this->mapTTL;
    }
}
