<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use MyCLabs\Enum\Enum;
use PackageVersions\Versions;
use Psr\Cache\CacheItemPoolInterface;
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
use TheCodingMachine\GraphQLite\Utils\Namespaces\NamespaceFactory;

use function array_map;
use function array_reverse;
use function class_exists;
use function md5;
use function substr;

/**
 * A class to help getting started with GraphQLite.
 * It is in charge of creating a schema with most sensible defaults.
 */
class SchemaFactory
{
    public const GLOB_CACHE_SECONDS = 2;

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
    /** @var int|null */
    private $globTTL = self::GLOB_CACHE_SECONDS;
    /** @var array<int, FieldMiddlewareInterface> */
    private $fieldMiddlewares = [];
    /** @var ExpressionLanguage|null */
    private $expressionLanguage;
    /** @var string */
    private $cacheNamespace;

    public function __construct(CacheInterface $cache, ContainerInterface $container)
    {
        $this->cacheNamespace = substr(md5(Versions::getVersion('thecodingmachine/graphqlite')), 0, 8);
        $this->cache = $cache;
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
    private function getDoctrineAnnotationReader(CacheItemPoolInterface $cache): Reader
    {
        if ($this->doctrineAnnotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');

            return new PsrCachedReader(new DoctrineAnnotationReader(), $cache, true);
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
    public function setGlobTTL(?int $globTTL): self
    {
        $this->globTTL = $globTTL;

        return $this;
    }

    /**
     * Sets GraphQLite in "prod" mode (cache settings optimized for best performance).
     *
     * This is a shortcut for `$schemaFactory->setGlobTTL(null)`
     */
    public function prodMode(): self
    {
        return $this->setGlobTTL(null);
    }

    /**
     * Sets GraphQLite in "dev" mode (this is the default mode: cache settings optimized for best developer experience).
     *
     * This is a shortcut for `$schemaFactory->setGlobTTL(2)`
     */
    public function devMode(): self
    {
        return $this->setGlobTTL(self::GLOB_CACHE_SECONDS);
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
        $symfonyCache          = new Psr16Adapter($this->cache, $this->cacheNamespace);
        $annotationReader      = new AnnotationReader($this->getDoctrineAnnotationReader($symfonyCache), AnnotationReader::LAX_MODE);
        $authenticationService = $this->authenticationService ?: new FailAuthenticationService();
        $authorizationService  = $this->authorizationService ?: new FailAuthorizationService();
        $typeResolver          = new TypeResolver();
        $namespacedCache       = new NamespacedCache($this->cache);
        $cachedDocBlockFactory = new CachedDocBlockFactory($namespacedCache);
        $namingStrategy        = $this->namingStrategy ?: new NamingStrategy();
        $typeRegistry          = new TypeRegistry();

        $namespaceFactory = new NamespaceFactory($namespacedCache, $this->classNameMapper, $this->globTTL);
        $nsList = array_map(static function (string $namespace) use ($namespaceFactory) {
            return $namespaceFactory->createNamespace($namespace);
        }, $this->typeNamespaces);

        $expressionLanguage = $this->expressionLanguage ?: new ExpressionLanguage($symfonyCache);
        $expressionLanguage->registerProvider(new SecurityExpressionLanguageProvider());

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        foreach ($this->fieldMiddlewares as $fieldMiddleware) {
            $fieldMiddlewarePipe->pipe($fieldMiddleware);
        }
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityFieldMiddleware)
        $fieldMiddlewarePipe->pipe(new SecurityFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware($authenticationService, $authorizationService));

        $compositeTypeMapper = new CompositeTypeMapper();
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $namespacedCache, $typeRegistry);

        $topRootTypeMapper = new NullableTypeMapperAdapter();

        $errorRootTypeMapper = new FinalRootTypeMapper($recursiveTypeMapper);
        $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $recursiveTypeMapper, $topRootTypeMapper);
        if (class_exists(Enum::class)) {
            $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper, $annotationReader, $symfonyCache, $nsList);
        }

        if (! empty($this->rootTypeMapperFactories)) {
            $rootSchemaFactoryContext = new RootTypeMapperFactoryContext(
                $annotationReader,
                $typeResolver,
                $namingStrategy,
                $typeRegistry,
                $recursiveTypeMapper,
                $this->container,
                $namespacedCache,
                $this->globTTL
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

        if (empty($this->typeNamespaces) && empty($this->typeMappers) && empty($this->typeMapperFactories)) {
            throw new GraphQLRuntimeException('Cannot create schema: no namespace for types found (You must call the SchemaFactory::addTypeNamespace() at least once).');
        }

        foreach ($nsList as $ns) {
            $compositeTypeMapper->addTypeMapper(new GlobTypeMapper(
                $ns,
                $typeGenerator,
                $inputTypeGenerator,
                $inputTypeUtils,
                $this->container,
                $annotationReader,
                $namingStrategy,
                $recursiveTypeMapper,
                $namespacedCache,
                $this->globTTL
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
                $namespacedCache,
                $this->globTTL
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
                $namespacedCache,
                $this->classNameMapper,
                $this->globTTL
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
