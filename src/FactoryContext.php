<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
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
        private AnnotationReader $annotationReader,
        private TypeResolver $typeResolver,
        private NamingStrategyInterface $namingStrategy,
        private TypeRegistry $typeRegistry,
        private FieldsBuilder $fieldsBuilder,
        private TypeGenerator $typeGenerator,
        private InputTypeGenerator $inputTypeGenerator,
        private RecursiveTypeMapperInterface $recursiveTypeMapper,
        private ContainerInterface $container,
        private CacheInterface $cache,
        private ?InputTypeValidatorInterface $inputTypeValidator,
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

    public function getInputTypeValidator(): ?InputTypeValidatorInterface
    {
        return $this->inputTypeValidator;
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
