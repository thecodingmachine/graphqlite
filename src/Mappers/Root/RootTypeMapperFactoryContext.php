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
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var TypeResolver */
    private $typeResolver;
    /** @var NamingStrategyInterface */
    private $namingStrategy;
    /** @var TypeRegistry */
    private $typeRegistry;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var ContainerInterface */
    private $container;
    /** @var CacheInterface */
    private $cache;
    /** @var int|null */
    private $globTtl;
    /** @var int|null */
    private $mapTtl;

    public function __construct(
        AnnotationReader $annotationReader,
        TypeResolver $typeResolver,
        NamingStrategyInterface $namingStrategy,
        TypeRegistry $typeRegistry,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        ContainerInterface $container,
        CacheInterface $cache,
        ?int $globTtl = 2,
        ?int $mapTtl = null
    ) {
        $this->annotationReader = $annotationReader;
        $this->typeResolver = $typeResolver;
        $this->namingStrategy = $namingStrategy;
        $this->typeRegistry = $typeRegistry;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->container = $container;
        $this->cache = $cache;
        $this->globTtl = $globTtl;
        $this->mapTtl = $mapTtl;
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

    public function getGlobTtl(): ?int
    {
        return $this->globTtl;
    }

    public function getMapTtl(): ?int
    {
        return $this->mapTtl;
    }
}
