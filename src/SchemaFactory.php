<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use GraphQL\Type\SchemaConfig;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\CompositeParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use function function_exists;

/**
 * A class to help getting started with GraphQLite.
 * It is in charge of creating a schema with most sensible defaults.
 */
class SchemaFactory
{
    /** @var string[] */
    private $controllerNamespaces = [];
    /** @var string[] */
    private $typeNamespaces = [];
    /** @var QueryProviderInterface[] */
    private $queryProviders = [];
    /** @var RootTypeMapperInterface[] */
    private $rootTypeMappers = [];
    /** @var TypeMapperInterface[] */
    private $typeMappers = [];
    /** @var TypeMapperFactoryInterface[] */
    private $typeMapperFactories = [];
    /** @var ParameterMapperInterface[] */
    private $parameterMappers = [];
    /** @var Reader */
    private $doctrineAnnotationReader;
    /** @var AuthenticationServiceInterface|null */
    private $authenticationService;
    /** @var AuthorizationServiceInterface|null */
    private $authorizationService;
    /** @var CacheInterface */
    private $cache;
    /** @var NamingStrategyInterface|null */
    private $namingStrategy;
    /** @var ContainerInterface */
    private $container;
    /** @var SchemaConfig */
    private $schemaConfig;
    /** @var int */
    private $globTtl = 2;
    /** @var array<int, FieldMiddlewareInterface> */
    private $fieldMiddlewares = [];

    public function __construct(CacheInterface $cache, ContainerInterface $container)
    {
        $this->cache     = $cache;
        $this->container = $container;
    }

    /**
     * Registers a namespace that can contain GraphQL controllers.
     */
    public function addControllerNamespace(string $namespace): self
    {
        $this->controllerNamespaces[] = $namespace;

        return $this;
    }

    /**
     * Registers a namespace that can contain GraphQL types.
     */
    public function addTypeNamespace(string $namespace): self
    {
        $this->typeNamespaces[] = $namespace;

        return $this;
    }

    /**
     * Registers a query provider.
     */
    public function addQueryProvider(QueryProviderInterface $queryProvider): self
    {
        $this->queryProviders[] = $queryProvider;

        return $this;
    }

    /**
     * Registers a root type mapper.
     */
    public function addRootTypeMapper(RootTypeMapperInterface $rootTypeMapper): self
    {
        $this->rootTypeMappers[] = $rootTypeMapper;

        return $this;
    }

    /**
     * Registers a type mapper.
     */
    public function addTypeMapper(TypeMapperInterface $typeMapper): self
    {
        $this->typeMappers[] = $typeMapper;

        return $this;
    }

    /**
     * Registers a type mapper factory.
     */
    public function addTypeMapperFactory(TypeMapperFactoryInterface $typeMapperFactory): self
    {
        $this->typeMapperFactories[] = $typeMapperFactory;

        return $this;
    }

    public function setDoctrineAnnotationReader(Reader $annotationReader): self
    {
        $this->doctrineAnnotationReader = $annotationReader;

        return $this;
    }

    /**
     * Returns a cached Doctrine annotation reader.
     * Note: we cannot get the annotation reader service in the container as we are in a compiler pass.
     */
    private function getDoctrineAnnotationReader(): Reader
    {
        if ($this->doctrineAnnotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $doctrineAnnotationReader = new DoctrineAnnotationReader();

            if (function_exists('apcu_fetch')) {
                $doctrineAnnotationReader = new CachedReader($doctrineAnnotationReader, new ApcuCache(), true);
            }

            return $doctrineAnnotationReader;
        }

        return $this->doctrineAnnotationReader;
    }

    public function setAuthenticationService(AuthenticationServiceInterface $authenticationService): self
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    public function setAuthorizationService(AuthorizationServiceInterface $authorizationService): self
    {
        $this->authorizationService = $authorizationService;

        return $this;
    }

    public function setNamingStrategy(NamingStrategyInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    public function setSchemaConfig(SchemaConfig $schemaConfig): self
    {
        $this->schemaConfig = $schemaConfig;

        return $this;
    }

    /**
     * Sets the time to live time of the cache for annotations in files.
     * By default this is set to 2 seconds which is ok for development environments.
     * Set this to "null" (i.e. infinity) for production environments.
     */
    public function setGlobTtl(?int $globTtl): self
    {
        $this->globTtl = $globTtl;

        return $this;
    }

    /**
     * Sets GraphQLite in "prod" mode (cache settings optimized for best performance).
     *
     * This is a shortcut for `$schemaFactory->setGlobTtl(null)`
     */
    public function prodMode(): self
    {
        return $this->setGlobTtl(null);
    }

    /**
     * Sets GraphQLite in "dev" mode (this is the default mode: cache settings optimized for best developer experience).
     *
     * This is a shortcut for `$schemaFactory->setGlobTtl(2)`
     */
    public function devMode(): self
    {
        return $this->setGlobTtl(2);
    }

    /**
     * Registers a field middleware (used to parse custom annotations that modify the GraphQLite behaviour in Fields/Queries/Mutations.
     */
    public function addFieldMiddleware(FieldMiddlewareInterface $fieldMiddleware): self
    {
        $this->fieldMiddlewares[] = $fieldMiddleware;

        return $this;
    }

    public function createSchema(): Schema
    {
        $annotationReader      = new AnnotationReader($this->getDoctrineAnnotationReader(), AnnotationReader::LAX_MODE);
        $authenticationService = $this->authenticationService ?: new FailAuthenticationService();
        $authorizationService  = $this->authorizationService ?: new FailAuthorizationService();
        $typeResolver          = new TypeResolver();
        $cachedDocBlockFactory = new CachedDocBlockFactory($this->cache);
        $namingStrategy        = $this->namingStrategy ?: new NamingStrategy();
        $typeRegistry          = new TypeRegistry();

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        foreach ($this->fieldMiddlewares as $fieldMiddleware) {
            $fieldMiddlewarePipe->pipe($fieldMiddleware);
        }
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware($authenticationService, $authorizationService));

        $compositeTypeMapper = new CompositeTypeMapper();
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $this->cache, $typeRegistry);

        $rootTypeMappers   = $this->rootTypeMappers;
        $rootTypeMappers[] = new MyCLabsEnumTypeMapper();
        $rootTypeMappers[] = new BaseTypeMapper($recursiveTypeMapper);
        // Let's put all the root type mappers except the BaseTypeMapper (that needs a recursive type mapper and that will be built later)
        $compositeRootTypeMapper = new CompositeRootTypeMapper($rootTypeMappers);

        $argumentResolver = new ArgumentResolver();

        $parameterMappers         = $this->parameterMappers;
        $parameterMappers[]       = new ResolveInfoParameterMapper();
        $compositeParameterMapper = new CompositeParameterMapper($parameterMappers);

        $fieldsBuilder = new FieldsBuilder(
            $annotationReader,
            $recursiveTypeMapper,
            $argumentResolver,
            $typeResolver,
            $cachedDocBlockFactory,
            $namingStrategy,
            $compositeRootTypeMapper,
            $compositeParameterMapper,
            $fieldMiddlewarePipe
        );

        $typeGenerator      = new TypeGenerator($annotationReader, $namingStrategy, $typeRegistry, $this->container, $recursiveTypeMapper, $fieldsBuilder);
        $inputTypeUtils     = new InputTypeUtils($annotationReader, $namingStrategy);
        $inputTypeGenerator = new InputTypeGenerator($inputTypeUtils, $fieldsBuilder);

        if (empty($this->typeNamespaces) && empty($this->typeMappers) && empty($this->typeMapperFactories)) {
            throw new GraphQLException('Cannot create schema: no namespace for types found (You must call the SchemaFactory::addTypeNamespace() at least once).');
        }

        foreach ($this->typeNamespaces as $typeNamespace) {
            $compositeTypeMapper->addTypeMapper(new GlobTypeMapper(
                $typeNamespace,
                $typeGenerator,
                $inputTypeGenerator,
                $inputTypeUtils,
                $this->container,
                $annotationReader,
                $namingStrategy,
                $recursiveTypeMapper,
                $this->cache,
                $this->globTtl
            ));
        }

        foreach ($this->typeMappers as $typeMapper) {
            $compositeTypeMapper->addTypeMapper($typeMapper);
        }

        foreach ($this->typeMapperFactories as $typeMapperFactory) {
            $compositeTypeMapper->addTypeMapper($typeMapperFactory->create($recursiveTypeMapper));
        }

        $compositeTypeMapper->addTypeMapper(new PorpaginasTypeMapper($recursiveTypeMapper));

        $queryProviders = [];
        foreach ($this->controllerNamespaces as $controllerNamespace) {
            $queryProviders[] = new GlobControllerQueryProvider(
                $controllerNamespace,
                $fieldsBuilder,
                $this->container,
                $this->cache,
                $this->globTtl
            );
        }

        foreach ($this->queryProviders as $queryProvider) {
            $queryProviders[] = $queryProvider;
        }

        if ($queryProviders === []) {
            throw new GraphQLException('Cannot create schema: no namespace for controllers found (You must call the SchemaFactory::addControllerNamespace() at least once).');
        }

        $aggregateQueryProvider = new AggregateQueryProvider($queryProviders);

        return new Schema($aggregateQueryProvider, $recursiveTypeMapper, $typeResolver, $this->schemaConfig, $compositeRootTypeMapper);
    }
}
