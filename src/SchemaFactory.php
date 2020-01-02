<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\PhpFileCache;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use PackageVersions\Versions;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\InjectUserParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddleware;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\NamespacedCache;
use function array_reverse;
use function crc32;
use function function_exists;
use function md5;
use function substr;
use function sys_get_temp_dir;

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
    /** @var QueryProviderFactoryInterface[] */
    private $queryProviderFactories = [];
    /** @var RootTypeMapperFactoryInterface[] */
    private $rootTypeMapperFactories = [];
    /** @var TypeMapperInterface[] */
    private $typeMappers = [];
    /** @var TypeMapperFactoryInterface[] */
    private $typeMapperFactories = [];
    /** @var ParameterMiddlewareInterface[] */
    private $parameterMiddlewares = [];
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
    /** @var ClassNameMapper */
    private $classNameMapper;
    /** @var SchemaConfig */
    private $schemaConfig;
    /** @var int */
    private $globTtl = 2;
    /** @var array<int, FieldMiddlewareInterface> */
    private $fieldMiddlewares = [];
    /** @var ExpressionLanguage|null */
    private $expressionLanguage;

    public function __construct(CacheInterface $cache, ContainerInterface $container)
    {
        $this->cache     = new NamespacedCache($cache);
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
     * Registers a query provider factory.
     */
    public function addQueryProviderFactory(QueryProviderFactoryInterface $queryProviderFactory): self
    {
        $this->queryProviderFactories[] = $queryProviderFactory;

        return $this;
    }

    /**
     * Registers a root type mapper factory.
     */
    public function addRootTypeMapperFactory(RootTypeMapperFactoryInterface $rootTypeMapperFactory): self
    {
        $this->rootTypeMapperFactories[] = $rootTypeMapperFactory;

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

    /**
     * Registers a parameter middleware.
     */
    public function addParameterMiddleware(ParameterMiddlewareInterface $parameterMiddleware): self
    {
        $this->parameterMiddlewares[] = $parameterMiddleware;

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

            $cache = function_exists('apcu_fetch') ? new ApcuCache() : new PhpFileCache(sys_get_temp_dir() . '/graphqlite.' . crc32(__DIR__));

            $namespace = substr(md5(Versions::getVersion('thecodingmachine/graphqlite')), 0, 8);
            $cache->setNamespace($namespace);

            $doctrineAnnotationReader = new CachedReader($doctrineAnnotationReader, $cache, true);

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

    public function setClassNameMapper(ClassNameMapper $classNameMapper): self
    {
        $this->classNameMapper = $classNameMapper;

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

    /**
     * Sets a custom expression language to use.
     * ExpressionLanguage is used to evaluate expressions in the "Security" tag.
     */
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage): self
    {
        $this->expressionLanguage = $expressionLanguage;

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

        $psr6Cache = new Psr16Adapter($this->cache);
        $expressionLanguage = $this->expressionLanguage ?: new ExpressionLanguage($psr6Cache);
        $expressionLanguage->registerProvider(new SecurityExpressionLanguageProvider());

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        foreach ($this->fieldMiddlewares as $fieldMiddleware) {
            $fieldMiddlewarePipe->pipe($fieldMiddleware);
        }
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityFieldMiddleware)
        $fieldMiddlewarePipe->pipe(new SecurityFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware($authenticationService, $authorizationService));

        $compositeTypeMapper = new CompositeTypeMapper();
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $this->cache, $typeRegistry);

        $topRootTypeMapper = new NullableTypeMapperAdapter();

        $errorRootTypeMapper = new FinalRootTypeMapper($recursiveTypeMapper);
        $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $recursiveTypeMapper, $topRootTypeMapper);
        $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper);

        if (! empty($this->rootTypeMapperFactories)) {
            $rootSchemaFactoryContext = new RootTypeMapperFactoryContext(
                $annotationReader,
                $typeResolver,
                $namingStrategy,
                $typeRegistry,
                $recursiveTypeMapper,
                $this->container,
                $this->cache
            );

            $reversedRootTypeMapperFactories = array_reverse($this->rootTypeMapperFactories);
            foreach ($reversedRootTypeMapperFactories as $rootTypeMapperFactory) {
                $rootTypeMapper = $rootTypeMapperFactory->create($rootTypeMapper, $rootSchemaFactoryContext);
            }
        }

        $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $topRootTypeMapper, $typeRegistry, $recursiveTypeMapper);
        $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $topRootTypeMapper);

        $topRootTypeMapper->setNext($rootTypeMapper);

        $argumentResolver = new ArgumentResolver();

        $parameterMiddlewarePipe = new ParameterMiddlewarePipe();
        foreach ($this->parameterMiddlewares as $parameterMapper) {
            $parameterMiddlewarePipe->pipe($parameterMapper);
        }
        $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
        $parameterMiddlewarePipe->pipe(new ContainerParameterHandler($this->container));
        $parameterMiddlewarePipe->pipe(new InjectUserParameterHandler($authenticationService));

        $fieldsBuilder = new FieldsBuilder(
            $annotationReader,
            $recursiveTypeMapper,
            $argumentResolver,
            $typeResolver,
            $cachedDocBlockFactory,
            $namingStrategy,
            $topRootTypeMapper,
            $parameterMiddlewarePipe,
            $fieldMiddlewarePipe
        );

        $typeGenerator      = new TypeGenerator($annotationReader, $namingStrategy, $typeRegistry, $this->container, $recursiveTypeMapper, $fieldsBuilder);
        $inputTypeUtils     = new InputTypeUtils($annotationReader, $namingStrategy);
        $inputTypeGenerator = new InputTypeGenerator($inputTypeUtils, $fieldsBuilder);

        if (empty($this->typeNamespaces) && empty($this->typeMappers)) {
            throw new GraphQLRuntimeException('Cannot create schema: no namespace for types found (You must call the SchemaFactory::addTypeNamespace() at least once).');
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
                $this->classNameMapper,
                $this->globTtl
            ));
        }

        foreach ($this->typeMappers as $typeMapper) {
            $compositeTypeMapper->addTypeMapper($typeMapper);
        }

        if (! empty($this->typeMapperFactories) || ! empty($this->queryProviderFactories)) {
            $context = new FactoryContext(
                $annotationReader,
                $typeResolver,
                $namingStrategy,
                $typeRegistry,
                $fieldsBuilder,
                $typeGenerator,
                $inputTypeGenerator,
                $recursiveTypeMapper,
                $this->container,
                $this->cache
            );
        }

        foreach ($this->typeMapperFactories as $typeMapperFactory) {
            $compositeTypeMapper->addTypeMapper($typeMapperFactory->create($context));
        }

        $compositeTypeMapper->addTypeMapper(new PorpaginasTypeMapper($recursiveTypeMapper));

        $queryProviders = [];
        foreach ($this->controllerNamespaces as $controllerNamespace) {
            $queryProviders[] = new GlobControllerQueryProvider(
                $controllerNamespace,
                $fieldsBuilder,
                $this->container,
                $annotationReader,
                $this->cache,
                $this->classNameMapper,
                $this->globTtl
            );
        }

        foreach ($this->queryProviders as $queryProvider) {
            $queryProviders[] = $queryProvider;
        }

        foreach ($this->queryProviderFactories as $queryProviderFactory) {
            $queryProviders[] = $queryProviderFactory->create($context);
        }

        if ($queryProviders === []) {
            throw new GraphQLRuntimeException('Cannot create schema: no namespace for controllers found (You must call the SchemaFactory::addControllerNamespace() at least once).');
        }

        $aggregateQueryProvider = new AggregateQueryProvider($queryProviders);

        return new Schema($aggregateQueryProvider, $recursiveTypeMapper, $typeResolver, $topRootTypeMapper, $this->schemaConfig);
    }
}
