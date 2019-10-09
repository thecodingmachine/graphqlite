<?php


namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Cache\ApcuCache;
use function extension_loaded;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use function sys_get_temp_dir;
use TheCodingMachine\GraphQLite\Hydrators\FactoryHydrator;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

/**
 * A class to help getting started with GraphQLite.
 * It is in charge of creating a schema with most sensible defaults.
 */
class SchemaFactory
{
    private $controllerNamespaces = [];
    private $typeNamespaces = [];
    /**
     * @var QueryProviderInterface[]
     */
    private $queryProviders = [];
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];
    private $doctrineAnnotationReader;
    /**
     * @var HydratorInterface|null
     */
    private $hydrator;
    /**
     * @var AuthenticationServiceInterface|null
     */
    private $authenticationService;
    /**
     * @var AuthorizationServiceInterface|null
     */
    private $authorizationService;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var NamingStrategyInterface|null
     */
    private $namingStrategy;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ClassNameMapper
     */
    private $classNameMapper;
    /**
     * @var SchemaConfig
     */
    private $schemaConfig;
    /**
     * @var LockFactory
     */
    private $lockFactory;

    public function __construct(CacheInterface $cache, ContainerInterface $container)
    {
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
     * Registers a type mapper.
     */
    public function addTypeMapper(TypeMapperInterface $typeMapper): self
    {
        $this->typeMappers[] = $typeMapper;
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

    public function setHydrator(HydratorInterface $hydrator): self
    {
        $this->hydrator = $hydrator;
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

    public function createSchema(): Schema
    {
        $annotationReader = new AnnotationReader($this->getDoctrineAnnotationReader(), AnnotationReader::LAX_MODE);
        $hydrator = $this->hydrator ?: new FactoryHydrator();
        $argumentResolver = new ArgumentResolver($hydrator);
        $authenticationService = $this->authenticationService ?: new FailAuthenticationService();
        $authorizationService = $this->authorizationService ?: new FailAuthorizationService();
        $typeResolver = new TypeResolver();
        $cachedDocBlockFactory = new CachedDocBlockFactory($this->cache);
        $namingStrategy = $this->namingStrategy ?: new NamingStrategy();
        $typeRegistry = new TypeRegistry();

        if (extension_loaded('sysvsem')) {
            $lockStore = new SemaphoreStore();
        } else {
            $lockStore = new FlockStore(sys_get_temp_dir());
        }
        $lockFactory = new LockFactory($lockStore);

        $fieldsBuilderFactory = new FieldsBuilderFactory($annotationReader, $hydrator, $authenticationService,
            $authorizationService, $typeResolver, $cachedDocBlockFactory, $namingStrategy);

        $typeGenerator = new TypeGenerator($annotationReader, $fieldsBuilderFactory, $namingStrategy, $typeRegistry, $this->container);
        $inputTypeUtils = new InputTypeUtils($annotationReader, $namingStrategy);
        $inputTypeGenerator = new InputTypeGenerator($inputTypeUtils, $fieldsBuilderFactory, $argumentResolver);

        $typeMappers = [];

        foreach ($this->typeNamespaces as $typeNamespace) {
            $typeMappers[] = new GlobTypeMapper($typeNamespace, $typeGenerator, $inputTypeGenerator, $inputTypeUtils,
                $this->container, $annotationReader, $namingStrategy, $lockFactory, $this->cache, $this->classNameMapper);
        }

        foreach ($this->typeMappers as $typeMapper) {
            $typeMappers[] = $typeMapper;
        }

        if ($typeMappers === []) {
            throw new GraphQLException('Cannot create schema: no namespace for types found (You must call the SchemaFactory::addTypeNamespace() at least once).');
        }

        $typeMappers[] = new PorpaginasTypeMapper();

        $compositeTypeMapper = new CompositeTypeMapper($typeMappers);
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $this->cache, $typeRegistry);

        $queryProviders = [];
        foreach ($this->controllerNamespaces as $controllerNamespace) {
            $queryProviders[] = new GlobControllerQueryProvider($controllerNamespace, $fieldsBuilderFactory, $recursiveTypeMapper,
                $this->container, $lockFactory, $this->cache, $this->classNameMapper);
        }

        foreach ($this->queryProviders as $queryProvider) {
            $queryProviders[] = $queryProvider;
        }

        if ($queryProviders === []) {
            throw new GraphQLException('Cannot create schema: no namespace for controllers found (You must call the SchemaFactory::addControllerNamespace() at least once).');
        }

        // TODO: configure ttl for cache?

        $aggregateQueryProvider = new AggregateQueryProvider($queryProviders);

        return new Schema($aggregateQueryProvider, $recursiveTypeMapper, $typeResolver, $this->schemaConfig);
    }
}
