<?php

namespace TheCodingMachine\GraphQLite\Integration;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use Mouf\Picotainer\Picotainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\InjectUserParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddleware;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeMismatchRuntimeException;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use function var_dump;
use function var_export;

class EndToEndTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $mainContainer;

    public function setUp(): void
    {
        $this->mainContainer = $this->createContainer();
    }

    /**
     * @param array<string, callable> $overloadedServices
     */
    public function createContainer(array $overloadedServices = []): ContainerInterface
    {
        $services = [
            Schema::class => function(ContainerInterface $container) {
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RecursiveTypeMapperInterface::class), $container->get(TypeResolver::class), $container->get(RootTypeMapperInterface::class));
            },
            QueryProviderInterface::class => function(ContainerInterface $container) {
                return new GlobControllerQueryProvider('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers', $container->get(FieldsBuilder::class),
                    $container->get(BasicAutoWiringContainer::class), $container->get(AnnotationReader::class), new Psr16Cache(new ArrayAdapter()));
            },
            FieldsBuilder::class => function(ContainerInterface $container) {
                return new FieldsBuilder(
                    $container->get(AnnotationReader::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(ArgumentResolver::class),
                    $container->get(TypeResolver::class),
                    $container->get(CachedDocBlockFactory::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RootTypeMapperInterface::class),
                    $container->get(ParameterMiddlewareInterface::class),
                    $container->get(FieldMiddlewareInterface::class)
                );
            },
            FieldMiddlewareInterface::class => function(ContainerInterface $container) {
                $pipe = new FieldMiddlewarePipe();
                $pipe->pipe($container->get(AuthorizationFieldMiddleware::class));
                $pipe->pipe($container->get(SecurityFieldMiddleware::class));
                return $pipe;
            },
            AuthorizationFieldMiddleware::class => function(ContainerInterface $container) {
                return new AuthorizationFieldMiddleware(
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class)
                );
            },
            SecurityFieldMiddleware::class => function(ContainerInterface $container) {
                return new SecurityFieldMiddleware(
                    new ExpressionLanguage(new Psr16Adapter(new Psr16Cache(new ArrayAdapter())), [new SecurityExpressionLanguageProvider()]),
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class)
                );
            },
            ArgumentResolver::class => function(ContainerInterface $container) {
                return new ArgumentResolver();
            },
            TypeResolver::class => function(ContainerInterface $container) {
                return new TypeResolver();
            },
            BasicAutoWiringContainer::class => function(ContainerInterface $container) {
                return new BasicAutoWiringContainer(new EmptyContainer());
            },
            AuthorizationServiceInterface::class => function(ContainerInterface $container) {
                return new VoidAuthorizationService();
            },
            AuthenticationServiceInterface::class => function(ContainerInterface $container) {
                return new VoidAuthenticationService();
            },
            RecursiveTypeMapperInterface::class => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new RecursiveTypeMapper(
                    $container->get(TypeMapperInterface::class),
                    $container->get(NamingStrategyInterface::class),
                    new Psr16Cache($arrayAdapter),
                    $container->get(TypeRegistry::class)
                );
            },
            TypeMapperInterface::class => function(ContainerInterface $container) {
                return new CompositeTypeMapper();
            },
            GlobTypeMapper::class => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new GlobTypeMapper('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    new Psr16Cache($arrayAdapter)
                );
            },
            GlobTypeMapper::class.'2' => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new GlobTypeMapper('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    new Psr16Cache($arrayAdapter)
                );
            },
            PorpaginasTypeMapper::class => function(ContainerInterface $container) {
                return new PorpaginasTypeMapper($container->get(RecursiveTypeMapperInterface::class));
            },
            TypeGenerator::class => function(ContainerInterface $container) {
                return new TypeGenerator(
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(TypeRegistry::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(FieldsBuilder::class)
                );
            },
            TypeRegistry::class => function() {
                return new TypeRegistry();
            },
            InputTypeGenerator::class => function(ContainerInterface $container) {
                return new InputTypeGenerator(
                    $container->get(InputTypeUtils::class),
                    $container->get(FieldsBuilder::class)
                );
            },
            InputTypeUtils::class => function(ContainerInterface $container) {
                return new InputTypeUtils(
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class)
                );
            },
            AnnotationReader::class => function(ContainerInterface $container) {
                return new AnnotationReader(new DoctrineAnnotationReader());
            },
            NamingStrategyInterface::class => function() {
                return new NamingStrategy();
            },
            CachedDocBlockFactory::class => function() {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new CachedDocBlockFactory(new Psr16Cache($arrayAdapter));
            },
            RootTypeMapperInterface::class => function(ContainerInterface $container) {
                return new NullableTypeMapperAdapter();
            },
            'rootTypeMapper' => function(ContainerInterface $container) {
                $errorRootTypeMapper = new FinalRootTypeMapper($container->get(RecursiveTypeMapperInterface::class));
                $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $container->get(RecursiveTypeMapperInterface::class), $container->get(RootTypeMapperInterface::class));
                $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper);
                $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $container->get(RootTypeMapperInterface::class), $container->get(TypeRegistry::class), $container->get(RecursiveTypeMapperInterface::class));
                $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $container->get(RootTypeMapperInterface::class));
                return $rootTypeMapper;
            },
            ContainerParameterHandler::class => function(ContainerInterface $container) {
                return new ContainerParameterHandler($container, true, true);
            },
            InjectUserParameterHandler::class => function(ContainerInterface $container) {
                return new InjectUserParameterHandler($container->get(AuthenticationServiceInterface::class));
            },
            'testService' => function() {
                return 'foo';
            },
            stdClass::class => function() {
                // Empty test service for autowiring
                return new stdClass();
            },
            ParameterMiddlewareInterface::class => function(ContainerInterface $container) {
                $parameterMiddlewarePipe = new ParameterMiddlewarePipe();
                $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
                $parameterMiddlewarePipe->pipe($container->get(ContainerParameterHandler::class));
                $parameterMiddlewarePipe->pipe($container->get(InjectUserParameterHandler::class));

                return $parameterMiddlewarePipe;
            }
        ];
        $container = new Picotainer($overloadedServices + $services);
        $container->get(TypeResolver::class)->registerSchema($container->get(Schema::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(GlobTypeMapper::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(GlobTypeMapper::class.'2'));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(PorpaginasTypeMapper::class));

        $container->get(RootTypeMapperInterface::class)->setNext($container->get('rootTypeMapper'));
        /*$container->get(CompositeRootTypeMapper::class)->addRootTypeMapper(new CompoundTypeMapper($container->get(RootTypeMapperInterface::class), $container->get(TypeRegistry::class), $container->get(RecursiveTypeMapperInterface::class)));
        $container->get(CompositeRootTypeMapper::class)->addRootTypeMapper(new IteratorTypeMapper($container->get(RootTypeMapperInterface::class), $container->get(TypeRegistry::class), $container->get(RecursiveTypeMapperInterface::class)));
        $container->get(CompositeRootTypeMapper::class)->addRootTypeMapper(new IteratorTypeMapper($container->get(RootTypeMapperInterface::class), $container->get(TypeRegistry::class), $container->get(RecursiveTypeMapperInterface::class)));
        $container->get(CompositeRootTypeMapper::class)->addRootTypeMapper(new MyCLabsEnumTypeMapper());
        $container->get(CompositeRootTypeMapper::class)->addRootTypeMapper(new BaseTypeMapper($container->get(RecursiveTypeMapperInterface::class), $container->get(RootTypeMapperInterface::class)));
*/
        return $container;
    }

    public function testEndToEnd(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $schema->assertValid();

        $queryString = '
        query {
            contacts {
                name
                company
                uppercaseName
                repeatName(prefix:"foo", suffix:"bar")
                repeatInnerName
                ... on User {
                    email
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            null,
            new Context()
        );

        $this->assertSame([
            'contacts' => [
                [
                    'name' => 'Joe',
                    'company' => 'Joe Ltd',
                    'uppercaseName' => 'JOE',
                    'repeatName' => 'fooJoebar',
                    'repeatInnerName' => 'Joe',
                ],
                [
                    'name' => 'Bill',
                    'company' => 'Bill Ltd',
                    'uppercaseName' => 'BILL',
                    'repeatName' => 'fooBillbar',
                    'repeatInnerName' => 'Bill',
                    'email' => 'bill@example.com'
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        // Let's redo this to test cache.
        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            null,
            new Context()
        );

        $this->assertSame([
            'contacts' => [
                [
                    'name' => 'Joe',
                    'company' => 'Joe Ltd',
                    'uppercaseName' => 'JOE',
                    'repeatName' => 'fooJoebar',
                    'repeatInnerName' => 'Joe',
                ],
                [
                    'name' => 'Bill',
                    'company' => 'Bill Ltd',
                    'uppercaseName' => 'BILL',
                    'repeatName' => 'fooBillbar',
                    'repeatInnerName' => 'Bill',
                    'email' => 'bill@example.com'
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testPrefetchException(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $schema->assertValid();

        $queryString = '
        query {
            contacts {
                name
                company
                uppercaseName
                repeatName(prefix:"foo", suffix:"bar")
                repeatInnerName
                ... on User {
                    email
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('When using "prefetch", you sure ensure that the GraphQL execution "context" (passed to the GraphQL::executeQuery method) is an instance of \\TheCodingMachine\\GraphQLite\\Context');
        $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndInputTypeDate()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation {
          saveBirthDate(birthDate: "1942-12-24 00:00:00")  {
            name
            birthDate
          }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'saveBirthDate' => [
                'name' => 'Bill',
                'birthDate' => '1942-12-24T00:00:00+00:00',
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndInputTypeDateAsParam()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation($birthDate: DateTime!) {
          saveBirthDate(birthDate: $birthDate) {
            name
            birthDate
          }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            null,
            null,
            [
                "birthDate" => "1942-12-24 00:00:00"
            ]
        );

        $this->assertSame([
            'saveBirthDate' => [
                'name' => 'Bill',
                'birthDate' => '1942-12-24T00:00:00+00:00',
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndInputType()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation {
          saveContact(
            contact: {
                name: "foo",
                birthDate: "1942-12-24 00:00:00",
                relations: [
                    {
                        name: "bar"
                    }
                ]
            }
          ) {
            name,
            birthDate,
            relations {
              name
            }
          }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'saveContact' => [
                'name' => 'foo',
                'birthDate' => '1942-12-24T00:00:00+00:00',
                'relations' => [
                    [
                        'name' => 'bar',
                    ]
                ]
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndPorpaginas(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contactsIterator {
                items(limit: 1, offset: 1) {
                    name
                    uppercaseName
                    ... on User {
                        email
                    }
                }
                count
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contactsIterator' => [
                'items' => [
                    [
                        'name' => 'Bill',
                        'uppercaseName' => 'BILL',
                        'email' => 'bill@example.com'
                    ]
                ],
                'count' => 2
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        // Let's redo this to test cache.
        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contactsIterator' => [
                'items' => [
                    [
                        'name' => 'Bill',
                        'uppercaseName' => 'BILL',
                        'email' => 'bill@example.com'
                    ]
                ],
                'count' => 2
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        // Let's run a query with no limit but an offset
        $invalidQueryString = '
        query {
            contactsIterator {
                items(offset: 1) {
                    name
                    ... on User {
                        email
                    }
                }
                count
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $invalidQueryString
        );

        $this->assertSame('In the items field of a result set, you cannot add a "offset" without also adding a "limit"', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);


        // Let's run a query with no limit offset
        $invalidQueryString = '
        query {
            contactsIterator {
                items {
                    name
                    ... on User {
                        email
                    }
                }
                count
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $invalidQueryString
        );

        $this->assertSame([
            'contactsIterator' => [
                'items' => [
                    [
                        'name' => 'Joe',
                    ],
                    [
                        'name' => 'Bill',
                        'email' => 'bill@example.com'
                    ]
                ],
                'count' => 2
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndPorpaginasOnScalarType(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contactsNamesIterator {
                items(limit: 1, offset: 1)
                count
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contactsNamesIterator' => [
                'items' => ['Bill'],
                'count' => 2
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    /**
     * This tests is used to be sure that the PorpaginasIterator types are not mixed up when cached (because it has a subtype)
     */
    public function testEndToEnd2Iterators(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contactsIterator {
                items(limit: 1, offset: 1) {
                    name
                    uppercaseName
                    ... on User {
                        email
                    }
                }
                count
            }
            
            products {
                items {
                    name
                    price
                    unauthorized
                }
                count            
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contactsIterator' => [
                'items' => [
                    [
                        'name' => 'Bill',
                        'uppercaseName' => 'BILL',
                        'email' => 'bill@example.com'
                    ]
                ],
                'count' => 2
            ],
            'products' => [
                'items' => [
                    [
                        'name' => 'Foo',
                        'price' => 42.0,
                        'unauthorized' => null
                    ]
                ],
                'count' => 1
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

    }

    public function testEndToEndStaticFactories(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoFilters(filter: {values: ["foo", "bar"], moreValues: [12, 42], evenMoreValues: [62]})
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoFilters' => [ "foo", "bar", "12", "42", "62" ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        // Call again to test GlobTypeMapper cache
        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoFilters' => [ "foo", "bar", "12", "42", "62" ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testNonNullableTypesWithOptionnalFactoryArguments(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoFilters
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoFilters' => []
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testNullableTypesWithOptionnalFactoryArguments(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoNullableFilters
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoNullableFilters' => null
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndResolveInfo(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoResolveInfo
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoResolveInfo' => 'echoResolveInfo'
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndRightIssues(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contacts {
                name
                onlyLogged
                secret
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('You need to be logged to access this field', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            contacts {
                secret
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('You do not have sufficient rights to access this field', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
    }

    public function testAutowireService(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contacts {
                injectService
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contacts' => [
                [
                    'injectService' => 'OK',
                ],
                [
                    'injectService' => 'OK',
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testParameterAnnotationsInSourceField(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contacts {
                injectServiceFromExternal
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contacts' => [
                [
                    'injectServiceFromExternal' => 'OK',
                ],
                [
                    'injectServiceFromExternal' => 'OK',
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndEnums(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoProductType(productType: NON_FOOD)
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoProductType' => 'NON_FOOD'
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndDateTime(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoDate(date: "2019-05-05T01:02:03+00:00")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoDate' => '2019-05-05T01:02:03+00:00'
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndErrorHandlingOfInconstentTypesInArrays(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            productsBadType {
                name
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(TypeMismatchRuntimeException::class);
        $this->expectExceptionMessage('In TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers\\ProductController::getProductsBadType() (declaring field "productsBadType"): Expected resolved value to be an object but got "array"');
        $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndNonDefaultOutputType(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            otherContact {
                name
                fullName
                phone
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'otherContact' => [
                'name' => 'Joe',
                'fullName' => 'JOE',
                'phone' => '0123456789'
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndSecurityAnnotation(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            secretPhrase(secret: "foo")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'secretPhrase' => 'you can see this secret only if passed parameter is "foo"'
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        $queryString = '
        query {
            secretPhrase(secret: "bar")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Wrong secret passed');
        $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndSecurityFailWithAnnotation(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        // Test with failWith attribute
        $queryString = '
        query {
            nullableSecretPhrase(secret: "bar")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'nullableSecretPhrase' => null
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);

        // Test with @FailWith annotation
        $queryString = '
        query {
            nullableSecretPhrase2(secret: "bar")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'nullableSecretPhrase2' => null
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndSecurityWithUser(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        // Test with failWith attribute
        $queryString = '
        query {
            secretUsingUser
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Access denied.', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
    }

    public function testEndToEndSecurityWithUserConnected(): void
    {
        $container = $this->createContainer([
            AuthenticationServiceInterface::class => static function() {
                return new class implements AuthenticationServiceInterface {
                    public function isLogged(): bool
                    {
                        return true;
                    }

                    public function getUser(): ?object
                    {
                        $user = new stdClass();
                        $user->bar = 42;
                        return $user;
                    }
                };
            },
            AuthorizationServiceInterface::class => static function() {
                return new class implements AuthorizationServiceInterface {
                    public function isAllowed(string $right, $subject = null): bool
                    {
                        if ($right === 'CAN_EDIT' && $subject->bar == 42) {
                            return true;
                        }
                        return false;
                    }
                };
            },


        ]);

        /**
         * @var Schema $schema
         */
        $schema = $container->get(Schema::class);

        // Test with failWith attribute
        $queryString = '
        query {
            secretUsingUser
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('you can see this secret only if user.bar is set to 42', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingUser']);


        // Test with failWith attribute
        $queryString = '
        query {
            secretUsingIsGranted
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('you can see this secret only if user has right "CAN_EDIT"', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingIsGranted']);
    }

    public function testEndToEndSecurityWithThis(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            secretUsingThis(secret:"41")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Access denied.', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            secretUsingThis(secret:"42")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('you can see this secret only if isAllowed() returns true', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingThis']);
    }

    public function testEndToEndSecurityInField(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            products {
                items {
                    margin(secret: "12")
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Access denied.', $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
    }

    public function testEndToEndUnions(){
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            getProduct{
                __typename
                ... on SpecialProduct{
                    name
                    special
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );
        $resultArray = $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS);

        $this->assertEquals('SpecialProduct', $resultArray['data']['getProduct']['__typename']);
        $this->assertEquals('Special box', $resultArray['data']['getProduct']['name']);
        $this->assertEquals('unicorn', $resultArray['data']['getProduct']['special']);
    }

    public function testEndToEndUnionsInIterables(){
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            getProducts2{
                __typename
                ... on SpecialProduct{
                    name
                    special
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );
        $resultArray = $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS);

        $this->assertEquals('SpecialProduct', $resultArray['data']['getProducts2'][0]['__typename']);
        $this->assertEquals('Special box', $resultArray['data']['getProducts2'][0]['name']);
        $this->assertEquals('unicorn', $resultArray['data']['getProducts2'][0]['special']);
    }

    public function testEndToEndMagicFieldWithPhpType(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contacts {
                magicContact {
                    name
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'contacts' => [
                [
                    'magicContact' => [
                        'name' => 'foo'
                    ]
                ],
                [
                    'magicContact' => [
                        'name' => 'foo'
                    ]
                ],
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testEndToEndInjectUser(): void
    {
        $container = $this->createContainer([
            AuthenticationServiceInterface::class => static function() {
                return new class implements AuthenticationServiceInterface {
                    public function isLogged(): bool
                    {
                        return true;
                    }

                    public function getUser(): ?object
                    {
                        $user = new stdClass();
                        $user->bar = 42;
                        return $user;
                    }
                };
            }
        ]);

        /**
         * @var Schema $schema
         */
        $schema = $container->get(Schema::class);

        // Test with failWith attribute
        $queryString = '
        query {
            injectedUser
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame(42, $result->toArray(Debug::RETHROW_UNSAFE_EXCEPTIONS)['data']['injectedUser']);
    }

    public function testInputOutputNameConflict(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $schemaFactory = new SchemaFactory(new Psr16Cache($arrayAdapter), new BasicAutoWiringContainer(new EmptyContainer()));
        $schemaFactory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\InputOutputNameConflict\\Controllers');
        $schemaFactory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\InputOutputNameConflict\\Types');

        $schema = $schemaFactory->createSchema();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $inAndOut, in TheCodingMachine\\GraphQLite\\Fixtures\\InputOutputNameConflict\\Controllers\\InAndOutController::testInAndOut, type "InAndOut" must be an input type (if you declared an input type with the name "InAndOut", make sure that there are no output type with the same name as this is forbidden by the GraphQL spec).');

        $schema->validate();
    }
}
