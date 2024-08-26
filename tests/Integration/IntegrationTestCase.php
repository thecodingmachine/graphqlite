<?php

namespace TheCodingMachine\GraphQLite\Integration;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use Kcs\ClassFinder\Finder\ComposerFinder;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\CacheUtils\ClassBoundCache;
use TheCodingMachine\CacheUtils\ClassBoundCacheContract;
use TheCodingMachine\CacheUtils\FileBoundCache;
use TheCodingMachine\GraphQLite\AggregateQueryProvider;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Containers\LazyContainer;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Discovery\KcsClassFinder;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;
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
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\EnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\LastDelegatingTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidTypeMapper;
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
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\ParameterizedCallableResolver;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockContextFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockContextFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockContextFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

class IntegrationTestCase extends TestCase
{
    protected ContainerInterface $mainContainer;

    public function setUp(): void
    {
        $this->mainContainer = $this->createContainer();
    }

    /** @param array<string, callable> $overloadedServices */
    public function createContainer(array $overloadedServices = []): ContainerInterface
    {
        $services = [
            Schema::class => static function (ContainerInterface $container) {
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RecursiveTypeMapperInterface::class), $container->get(TypeResolver::class), $container->get(RootTypeMapperInterface::class));
            },
            ClassFinder::class => function () {
                $composerFinder = new ComposerFinder();
                $composerFinder->inNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types');
                $composerFinder->inNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models');
                $composerFinder->inNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');

                return new KcsClassFinder($composerFinder);
            },
            ClassFinderComputedCache::class => function () {
                return new HardClassFinderComputedCache(
                    new Psr16Cache(new ArrayAdapter()),
                );
            },
            QueryProviderInterface::class => static function (ContainerInterface $container) {
                $queryProvider = new GlobControllerQueryProvider(
                    $container->get(FieldsBuilder::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(ClassFinder::class),
                    $container->get(ClassFinderComputedCache::class),
                );

                $queryProvider = new AggregateQueryProvider([
                    $queryProvider,
                    new GlobControllerQueryProvider(
                        $container->get(FieldsBuilder::class),
                        $container->get(BasicAutoWiringContainer::class),
                        $container->get(AnnotationReader::class),
                        $container->get(ClassFinder::class),
                        $container->get(ClassFinderComputedCache::class),
                    ),
                ]);

                return $queryProvider;
            },
            FieldsBuilder::class => static function (ContainerInterface $container) {
                $parameterMiddlewarePipe = $container->get(ParameterMiddlewareInterface::class);
                $fieldsBuilder = new FieldsBuilder(
                    $container->get(AnnotationReader::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(ArgumentResolver::class),
                    $container->get(TypeResolver::class),
                    $container->get(DocBlockFactory::class),
                    $container->get(DocBlockContextFactory::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RootTypeMapperInterface::class),
                    $parameterMiddlewarePipe,
                    $container->get(FieldMiddlewareInterface::class),
                    $container->get(InputFieldMiddlewareInterface::class),
                );
                $parameterizedCallableResolver = new ParameterizedCallableResolver($fieldsBuilder, $container);

                $parameterMiddlewarePipe->pipe(new PrefetchParameterMiddleware($parameterizedCallableResolver));

                return $fieldsBuilder;
            },
            FieldMiddlewareInterface::class => static function (ContainerInterface $container) {
                $pipe = new FieldMiddlewarePipe();
                $pipe->pipe($container->get(AuthorizationFieldMiddleware::class));
                $pipe->pipe($container->get(SecurityFieldMiddleware::class));
                $pipe->pipe($container->get(CostFieldMiddleware::class));
                return $pipe;
            },
            InputFieldMiddlewareInterface::class => static function (ContainerInterface $container) {
                $pipe = new InputFieldMiddlewarePipe();
                $pipe->pipe($container->get(AuthorizationInputFieldMiddleware::class));
                $pipe->pipe($container->get(SecurityInputFieldMiddleware::class));
                return $pipe;
            },
            AuthorizationInputFieldMiddleware::class => static function (ContainerInterface $container) {
                return new AuthorizationInputFieldMiddleware(
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                );
            },
            SecurityInputFieldMiddleware::class => static function (ContainerInterface $container) {
                return new SecurityInputFieldMiddleware(
                    new ExpressionLanguage(new Psr16Adapter(new Psr16Cache(new ArrayAdapter())), [new SecurityExpressionLanguageProvider()]),
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                );
            },
            AuthorizationFieldMiddleware::class => static function (ContainerInterface $container) {
                return new AuthorizationFieldMiddleware(
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                );
            },
            SecurityFieldMiddleware::class => static function (ContainerInterface $container) {
                return new SecurityFieldMiddleware(
                    new ExpressionLanguage(new Psr16Adapter(new Psr16Cache(new ArrayAdapter())), [new SecurityExpressionLanguageProvider()]),
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                );
            },
            CostFieldMiddleware::class => fn () => new CostFieldMiddleware(),
            ArgumentResolver::class => static function (ContainerInterface $container) {
                return new ArgumentResolver();
            },
            TypeResolver::class => static function (ContainerInterface $container) {
                return new TypeResolver();
            },
            BasicAutoWiringContainer::class => static function (ContainerInterface $container) {
                return new BasicAutoWiringContainer(new EmptyContainer());
            },
            AuthorizationServiceInterface::class => static function (ContainerInterface $container) {
                return new VoidAuthorizationService();
            },
            AuthenticationServiceInterface::class => static function (ContainerInterface $container) {
                return new VoidAuthenticationService();
            },
            RecursiveTypeMapperInterface::class => static function (ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new RecursiveTypeMapper(
                    $container->get(TypeMapperInterface::class),
                    $container->get(NamingStrategyInterface::class),
                    new Psr16Cache($arrayAdapter),
                    $container->get(TypeRegistry::class),
                    $container->get(AnnotationReader::class),
                );
            },
            TypeMapperInterface::class => static function (ContainerInterface $container) {
                return new CompositeTypeMapper();
            },
            ClassFinderTypeMapper::class => static function (ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new ClassFinderTypeMapper(
                    $container->get(ClassFinder::class),
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(ClassFinderComputedCache::class),
                );
            },
            PorpaginasTypeMapper::class => static function (ContainerInterface $container) {
                return new PorpaginasTypeMapper($container->get(RecursiveTypeMapperInterface::class));
            },
            EnumTypeMapper::class => static function (ContainerInterface $container) {
                return new EnumTypeMapper(
                    $container->get(RootTypeMapperInterface::class),
                    $container->get(AnnotationReader::class),
                    $container->get(ClassFinder::class),
                    $container->get(ClassFinderComputedCache::class),
                );
            },
            TypeGenerator::class => static function (ContainerInterface $container) {
                return new TypeGenerator(
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(TypeRegistry::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(FieldsBuilder::class),
                );
            },
            TypeRegistry::class => static function () {
                return new TypeRegistry();
            },
            InputTypeGenerator::class => static function (ContainerInterface $container) {
                return new InputTypeGenerator(
                    $container->get(InputTypeUtils::class),
                    $container->get(FieldsBuilder::class),
                );
            },
            InputTypeUtils::class => static function (ContainerInterface $container) {
                return new InputTypeUtils(
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                );
            },
            AnnotationReader::class => static function (ContainerInterface $container) {
                return new AnnotationReader(new DoctrineAnnotationReader());
            },
            NamingStrategyInterface::class => static function () {
                return new NamingStrategy();
            },
            'nonInheritedClassBoundCacheContract' => static function () {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                $psr16Cache = new Psr16Cache($arrayAdapter);

                return new ClassBoundCacheContract(
                    new ClassBoundCache(
                        fileBoundCache: new FileBoundCache($psr16Cache),
                        analyzeParentClasses: false,
                        analyzeTraits: false,
                        analyzeInterfaces: false,
                    ),
                );
            },
            DocBlockFactory::class => static function (ContainerInterface $container) {
                return new CachedDocBlockFactory(
                    $container->get('nonInheritedClassBoundCacheContract'),
                    new PhpDocumentorDocBlockFactory(
                        \phpDocumentor\Reflection\DocBlockFactory::createInstance(),
                        $container->get(DocBlockContextFactory::class),
                    )
                );
            },
            DocBlockContextFactory::class => static function (ContainerInterface $container) {
                return new CachedDocBlockContextFactory(
                    $container->get('nonInheritedClassBoundCacheContract'),
                    new PhpDocumentorDocBlockContextFactory(
                        new ContextFactory(),
                    )
                );
            },
            RootTypeMapperInterface::class => static function (ContainerInterface $container) {
                return new VoidTypeMapper(
                    new NullableTypeMapperAdapter(
                        $container->get('topRootTypeMapper')
                    )
                );
            },
            'topRootTypeMapper' => static function () {
                return new LastDelegatingTypeMapper();
            },
            'rootTypeMapper' => static function (ContainerInterface $container) {
                // These are in reverse order of execution
                $errorRootTypeMapper = new FinalRootTypeMapper($container->get(RecursiveTypeMapperInterface::class));
                $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $container->get(RecursiveTypeMapperInterface::class), $container->get(RootTypeMapperInterface::class));
                $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper, $container->get(AnnotationReader::class), $container->get(ClassFinder::class), $container->get(ClassFinderComputedCache::class));
                $rootTypeMapper = new EnumTypeMapper($rootTypeMapper, $container->get(AnnotationReader::class), $container->get(DocBlockFactory::class), $container->get(ClassFinder::class), $container->get(ClassFinderComputedCache::class));
                $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $container->get(RootTypeMapperInterface::class), $container->get(NamingStrategyInterface::class), $container->get(TypeRegistry::class), $container->get(RecursiveTypeMapperInterface::class));
                $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $container->get(RootTypeMapperInterface::class));
                return $rootTypeMapper;
            },
            ContainerParameterHandler::class => static function (ContainerInterface $container) {
                return new ContainerParameterHandler($container, true, true);
            },
            InjectUserParameterHandler::class => static function (ContainerInterface $container) {
                return new InjectUserParameterHandler($container->get(AuthenticationServiceInterface::class));
            },
            'testService' => static function () {
                return 'foo';
            },
            stdClass::class => static function () {
                // Empty test service for autowiring
                return new stdClass();
            },
            ParameterMiddlewareInterface::class => static function (ContainerInterface $container) {
                $parameterMiddlewarePipe = new ParameterMiddlewarePipe();
                $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
                $parameterMiddlewarePipe->pipe($container->get(ContainerParameterHandler::class));
                $parameterMiddlewarePipe->pipe($container->get(InjectUserParameterHandler::class));

                return $parameterMiddlewarePipe;
            },
        ];

        $container = new LazyContainer($overloadedServices + $services);
        $container->get(TypeResolver::class)->registerSchema($container->get(Schema::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(ClassFinderTypeMapper::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(PorpaginasTypeMapper::class));

        $container->get('topRootTypeMapper')->setNext($container->get('rootTypeMapper'));

        return $container;
    }

    protected function getSuccessResult(ExecutionResult $result, int $debugFlag = DebugFlag::RETHROW_INTERNAL_EXCEPTIONS): mixed
    {
        $array = $result->toArray($debugFlag);
        if (isset($array['errors']) || ! isset($array['data'])) {
            $this->fail('Expected a successful answer. Got ' . json_encode($array, JSON_PRETTY_PRINT));
        }
        return $array['data'];
    }
}
