<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\SchemaConfig;
use Kcs\ClassFinder\FileFinder\CachedFileFinder;
use Kcs\ClassFinder\FileFinder\DefaultFileFinder;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Finder\FinderInterface;
use MyCLabs\Enum\Enum;
use PackageVersions\Versions;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Cache\ClassBoundCache;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Cache\SnapshotClassBoundCache;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\Cache\SnapshotClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Discovery\KcsClassFinder;
use TheCodingMachine\GraphQLite\Discovery\StaticClassFinder;
use TheCodingMachine\GraphQLite\Mappers\ClassFinderTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\InjectUserParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Parameters\PrefetchParameterMiddleware;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\EnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\LastDelegatingTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationInputFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\CostFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\SecurityInputFieldMiddleware;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\InputTypeValidatorInterface;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\NamespacedCache;

use function array_reverse;
use function class_exists;
use function md5;
use function substr;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * A class to help getting started with GraphQLite.
 * It is in charge of creating a schema with most sensible defaults.
 */
class SchemaFactory
{
    /** @var array<int,string> */
    private array $namespaces = [];

    /** @var QueryProviderInterface[] */
    private array $queryProviders = [];

    /** @var QueryProviderFactoryInterface[] */
    private array $queryProviderFactories = [];

    /** @var RootTypeMapperFactoryInterface[] */
    private array $rootTypeMapperFactories = [];

    /** @var TypeMapperInterface[] */
    private array $typeMappers = [];

    /** @var TypeMapperFactoryInterface[] */
    private array $typeMapperFactories = [];

    /** @var ParameterMiddlewareInterface[] */
    private array $parameterMiddlewares = [];

    private AuthenticationServiceInterface|null $authenticationService = null;

    private AuthorizationServiceInterface|null $authorizationService = null;

    private InputTypeValidatorInterface|null $inputTypeValidator = null;

    private NamingStrategyInterface|null $namingStrategy = null;

    private ClassFinder|FinderInterface|null $finder = null;

    private SchemaConfig|null $schemaConfig = null;

    private bool $devMode = true;

    /** @var array<int, FieldMiddlewareInterface> */
    private array $fieldMiddlewares = [];

    /** @var array<int, InputFieldMiddlewareInterface> */
    private array $inputFieldMiddlewares = [];

    private ExpressionLanguage|null $expressionLanguage = null;

    private string $cacheNamespace;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ContainerInterface $container,
        private ClassBoundCache|null $classBoundCache = null,
    ) {
        $this->cacheNamespace = substr(md5(Versions::getVersion('thecodingmachine/graphqlite')), 0, 8);
    }

    /**
     * Registers a namespace that can contain GraphQL controllers.
     *
     * @deprecated Using SchemaFactory::addControllerNamespace() is deprecated in favor of SchemaFactory::addNamespace()
     */
    public function addControllerNamespace(string $namespace): self
    {
        trigger_error(
            'Using SchemaFactory::addControllerNamespace() is deprecated in favor of SchemaFactory::addNamespace().',
            E_USER_DEPRECATED,
        );

        return $this->addNamespace($namespace);
    }

    /**
     * Registers a namespace that can contain GraphQL types.
     *
     * @deprecated Using SchemaFactory::addTypeNamespace() is deprecated in favor of SchemaFactory::addNamespace()
     */
    public function addTypeNamespace(string $namespace): self
    {
        trigger_error(
            'Using SchemaFactory::addTypeNamespace() is deprecated in favor of SchemaFactory::addNamespace().',
            E_USER_DEPRECATED,
        );

        return $this->addNamespace($namespace);
    }

    /**
     * Registers a namespace that can contain GraphQL types or controllers.
     */
    public function addNamespace(string $namespace): self
    {
        $this->namespaces[] = $namespace;

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

    public function setInputTypeValidator(InputTypeValidatorInterface|null $inputTypeValidator): self
    {
        $this->inputTypeValidator = $inputTypeValidator;

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

    public function setFinder(ClassFinder|FinderInterface $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * @deprecated setGlobTTL(null) or setGlobTTL(0) is equivalent to prodMode(), and any other values are equivalent to devMode()
     */
    public function setGlobTTL(int|null $globTTL): self
    {
        trigger_error(
            'Using SchemaFactory::setGlobTTL() is deprecated in favor of SchemaFactory::devMode() and SchemaFactory::prodMode().',
            E_USER_DEPRECATED,
        );

        return $globTTL ? $this->devMode() : $this->prodMode();
    }

    /**
     * Set a custom class bound cache. By default in dev mode it looks at file modification times.
     */
    public function setClassBoundCache(ClassBoundCache|null $classBoundCache): self
    {
        $this->classBoundCache = $classBoundCache;

        return $this;
    }

    /**
     * Sets GraphQLite in "prod" mode (cache settings optimized for best performance).
     */
    public function prodMode(): self
    {
        $this->devMode = false;

        return $this;
    }

    /**
     * Sets GraphQLite in "dev" mode (this is the default mode: cache settings optimized for best developer experience).
     */
    public function devMode(): self
    {
        $this->devMode = true;

        return $this;
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
     * Registers a input field middleware (used to parse custom annotations that modify the GraphQLite behaviour in Fields/Queries/Mutations.
     */
    public function addInputFieldMiddleware(InputFieldMiddlewareInterface $inputFieldMiddleware): self
    {
        $this->inputFieldMiddlewares[] = $inputFieldMiddleware;

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
        $symfonyCache = new Psr16Adapter($this->cache, $this->cacheNamespace);
        $annotationReader = new AnnotationReader();
        $authenticationService = $this->authenticationService ?: new FailAuthenticationService();
        $authorizationService = $this->authorizationService ?: new FailAuthorizationService();
        $typeResolver = new TypeResolver();
        $namespacedCache = new NamespacedCache($this->cache);
        $classBoundCache = $this->classBoundCache ?: new SnapshotClassBoundCache(
            $this->cache,
            $this->devMode ? FilesSnapshot::forClass(...) : FilesSnapshot::alwaysUnchanged(...),
        );
        $docBlockFactory = new CachedDocBlockFactory(
            $classBoundCache,
            PhpDocumentorDocBlockFactory::default(),
        );
        $namingStrategy = $this->namingStrategy ?: new NamingStrategy();
        $typeRegistry = new TypeRegistry();
        $classFinder = $this->createClassFinder();
        $classFinderComputedCache = $this->devMode ?
            new SnapshotClassFinderComputedCache($this->cache) :
            new HardClassFinderComputedCache($this->cache);

        $expressionLanguage = $this->expressionLanguage ?: new ExpressionLanguage($symfonyCache);
        $expressionLanguage->registerProvider(new SecurityExpressionLanguageProvider());

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        foreach ($this->fieldMiddlewares as $fieldMiddleware) {
            $fieldMiddlewarePipe->pipe($fieldMiddleware);
        }
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityFieldMiddleware)
        $fieldMiddlewarePipe->pipe(new SecurityFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware($authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new CostFieldMiddleware());

        $inputFieldMiddlewarePipe = new InputFieldMiddlewarePipe();
        foreach ($this->inputFieldMiddlewares as $inputFieldMiddleware) {
            $inputFieldMiddlewarePipe->pipe($inputFieldMiddleware);
        }
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityInputFieldMiddleware)
        $inputFieldMiddlewarePipe->pipe(new SecurityInputFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $inputFieldMiddlewarePipe->pipe(new AuthorizationInputFieldMiddleware($authenticationService, $authorizationService));

        $compositeTypeMapper = new CompositeTypeMapper();
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $namespacedCache, $typeRegistry, $annotationReader);

        $lastTopRootTypeMapper = new LastDelegatingTypeMapper();
        $topRootTypeMapper = new NullableTypeMapperAdapter($lastTopRootTypeMapper);
        $topRootTypeMapper = new VoidTypeMapper($topRootTypeMapper);

        $errorRootTypeMapper = new FinalRootTypeMapper($recursiveTypeMapper);
        $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $recursiveTypeMapper, $topRootTypeMapper);
        $rootTypeMapper = new EnumTypeMapper($rootTypeMapper, $annotationReader, $docBlockFactory, $classFinder, $classFinderComputedCache);

        if (class_exists(Enum::class)) {
            // Annotation support - deprecated
            $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper, $annotationReader, $classFinder, $classFinderComputedCache);
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
                classFinder: $classFinder,
                classFinderComputedCache: $classFinderComputedCache,
                classBoundCache: $classBoundCache,
            );

            $reversedRootTypeMapperFactories = array_reverse($this->rootTypeMapperFactories);
            foreach ($reversedRootTypeMapperFactories as $rootTypeMapperFactory) {
                $rootTypeMapper = $rootTypeMapperFactory->create($rootTypeMapper, $rootSchemaFactoryContext);
            }
        }

        $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $topRootTypeMapper, $namingStrategy, $typeRegistry, $recursiveTypeMapper);
        $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $topRootTypeMapper);

        $lastTopRootTypeMapper->setNext($rootTypeMapper);

        $argumentResolver = new ArgumentResolver();
        $parameterMiddlewarePipe = new ParameterMiddlewarePipe();

        $fieldsBuilder = new FieldsBuilder(
            $annotationReader,
            $recursiveTypeMapper,
            $argumentResolver,
            $typeResolver,
            $docBlockFactory,
            $namingStrategy,
            $topRootTypeMapper,
            $parameterMiddlewarePipe,
            $fieldMiddlewarePipe,
            $inputFieldMiddlewarePipe,
        );
        $parameterizedCallableResolver = new ParameterizedCallableResolver($fieldsBuilder, $this->container);

        foreach ($this->parameterMiddlewares as $parameterMapper) {
            $parameterMiddlewarePipe->pipe($parameterMapper);
        }
        $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
        $parameterMiddlewarePipe->pipe(new PrefetchParameterMiddleware($parameterizedCallableResolver));
        $parameterMiddlewarePipe->pipe(new ContainerParameterHandler($this->container));
        $parameterMiddlewarePipe->pipe(new InjectUserParameterHandler($authenticationService));

        $typeGenerator = new TypeGenerator($annotationReader, $namingStrategy, $typeRegistry, $this->container, $recursiveTypeMapper, $fieldsBuilder);
        $inputTypeUtils = new InputTypeUtils($annotationReader, $namingStrategy);
        $inputTypeGenerator = new InputTypeGenerator($inputTypeUtils, $fieldsBuilder, $this->inputTypeValidator);

        if ($this->namespaces) {
            $compositeTypeMapper->addTypeMapper(new ClassFinderTypeMapper(
                $classFinder,
                $typeGenerator,
                $inputTypeGenerator,
                $inputTypeUtils,
                $this->container,
                $annotationReader,
                $namingStrategy,
                $recursiveTypeMapper,
                $classFinderComputedCache,
            ));
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
                $this->inputTypeValidator,
                classFinder: $classFinder,
                classFinderComputedCache:  $classFinderComputedCache,
                classBoundCache: $classBoundCache,
            );
        }

        foreach ($this->typeMapperFactories as $typeMapperFactory) {
            $this->typeMappers[] = $typeMapperFactory->create($context);
        }

        if (empty($this->namespaces) && empty($this->typeMappers)) {
            throw new GraphQLRuntimeException('Cannot create schema: no namespace for types found (You must call the SchemaFactory::addNamespace() at least once).');
        }

        foreach ($this->typeMappers as $typeMapper) {
            $compositeTypeMapper->addTypeMapper($typeMapper);
        }

        $compositeTypeMapper->addTypeMapper(new PorpaginasTypeMapper($recursiveTypeMapper));

        $queryProviders = [];

        if ($this->namespaces) {
            $queryProviders[] = new GlobControllerQueryProvider(
                $fieldsBuilder,
                $this->container,
                $annotationReader,
                $classFinder,
                $classFinderComputedCache,
            );
        }

        foreach ($this->queryProviders as $queryProvider) {
            $queryProviders[] = $queryProvider;
        }

        foreach ($this->queryProviderFactories as $queryProviderFactory) {
            $queryProviders[] = $queryProviderFactory->create($context);
        }

        if ($queryProviders === []) {
            throw new GraphQLRuntimeException('Cannot create schema: no namespace for controllers found (You must call the SchemaFactory::addNamespace() at least once).');
        }

        $aggregateQueryProvider = new AggregateQueryProvider($queryProviders);

        return new Schema($aggregateQueryProvider, $recursiveTypeMapper, $typeResolver, $topRootTypeMapper, $this->schemaConfig);
    }

    private function createClassFinder(): ClassFinder
    {
        if ($this->finder instanceof ClassFinder) {
            return $this->finder;
        }

        // When no namespaces are specified, class finder uses all available namespaces to discover classes.
        // While this is technically okay, it doesn't follow SchemaFactory's semantics that allow it's
        // users to manually specify classes (see SchemaFactory::testCreateSchemaOnlyWithFactories()),
        // without having to specify namespaces to glob. This solves it by providing an empty iterator.
        if (! $this->namespaces) {
            return new StaticClassFinder([]);
        }

        $finder = (clone ($this->finder ?? new ComposerFinder()));

        // Because this finder may be iterated more than once, we need to make
        // sure that the filesystem is only hit once in the lifetime of the application,
        // as that may be expensive for larger projects or non-native filesystems.
        if ($finder instanceof ComposerFinder) {
            $finder = $finder->withFileFinder(new CachedFileFinder(new DefaultFileFinder(), new ArrayAdapter()));
        }

        foreach ($this->namespaces as $namespace) {
            $finder = $finder->inNamespace($namespace);
        }

        return new KcsClassFinder($finder);
    }
}
