<?php

namespace TheCodingMachine\GraphQLite\Integration;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Utils\SchemaPrinter;
use Mouf\Picotainer\Picotainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\AggregateQueryProvider;
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
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\EnumTypeMapper;
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
use TheCodingMachine\GraphQLite\Fixtures\Inputs\ValidationException;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\Validator;
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
use TheCodingMachine\GraphQLite\Utils\AccessPropertyException;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NamespaceFactory;
use UnitEnum;
use function json_encode;
use const JSON_PRETTY_PRINT;

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
                $queryProvider = new GlobControllerQueryProvider(
                    'TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers',
                    $container->get(FieldsBuilder::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    new Psr16Cache(new ArrayAdapter())
                );

                if (interface_exists(UnitEnum::class)) {
                    $queryProvider = new AggregateQueryProvider([
                        $queryProvider,
                        new GlobControllerQueryProvider(
                            'TheCodingMachine\\GraphQLite\\Fixtures81\\Integration\\Controllers',
                            $container->get(FieldsBuilder::class),
                            $container->get(BasicAutoWiringContainer::class),
                            $container->get(AnnotationReader::class),
                            new Psr16Cache(new ArrayAdapter())
                        )
                    ]);
                }
                return $queryProvider;
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
                    $container->get(TypeRegistry::class),
                    $container->get(AnnotationReader::class)
                );
            },
            TypeMapperInterface::class => function(ContainerInterface $container) {
                return new CompositeTypeMapper();
            },
            NamespaceFactory::class => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new NamespaceFactory(new Psr16Cache($arrayAdapter));
            },
            GlobTypeMapper::class => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new GlobTypeMapper($container->get(NamespaceFactory::class)->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types'),
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
            // We use a second type mapper here so we can target the Models dir
            GlobTypeMapper::class.'2' => function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new GlobTypeMapper($container->get(NamespaceFactory::class)->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models'),
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
            EnumTypeMapper::class => function(ContainerInterface $container) {
                return new EnumTypeMapper(
                    $container->get(RootTypeMapperInterface::class),
                    $container->get(AnnotationReader::class),
                    new ArrayAdapter(),
                    [
                        $container->get(NamespaceFactory::class)
                            ->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures81\\Integration\\Models')
                    ]
                );
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
                // These are in reverse order of execution
                $errorRootTypeMapper = new FinalRootTypeMapper($container->get(RecursiveTypeMapperInterface::class));
                $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $container->get(RecursiveTypeMapperInterface::class), $container->get(RootTypeMapperInterface::class));
                $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper, $container->get(AnnotationReader::class), new ArrayAdapter(), [ $container->get(NamespaceFactory::class)->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models') ]);
                if (interface_exists(UnitEnum::class)) {
                    $rootTypeMapper = new EnumTypeMapper($rootTypeMapper, $container->get(AnnotationReader::class), new ArrayAdapter(), [ $container->get(NamespaceFactory::class)->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures81\\Integration\\Models') ]);
                }
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

        if (interface_exists(UnitEnum::class)) {
            // Register another instance of GlobTypeMapper to process our PHP 8.1 enums and/or other
            // 8.1 supported features.
            $services[GlobTypeMapper::class.'3'] = function(ContainerInterface $container) {
                $arrayAdapter = new ArrayAdapter();
                $arrayAdapter->setLogger(new ExceptionLogger());
                return new GlobTypeMapper($container->get(NamespaceFactory::class)->createNamespace('TheCodingMachine\\GraphQLite\\Fixtures81\\Integration\\Models'),
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    new Psr16Cache($arrayAdapter)
                );
            };
        }

        $container = new Picotainer($overloadedServices + $services);
        $container->get(TypeResolver::class)->registerSchema($container->get(Schema::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(GlobTypeMapper::class));
        $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(GlobTypeMapper::class.'2'));
        if (interface_exists(UnitEnum::class)) {
            $container->get(TypeMapperInterface::class)->addTypeMapper($container->get(GlobTypeMapper::class.'3'));
        }
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

    /**
     * @return mixed
     */
    private function getSuccessResult(ExecutionResult $result, int $debugFlag = DebugFlag::RETHROW_INTERNAL_EXCEPTIONS) {
        $array = $result->toArray($debugFlag);
        if (isset($array['errors']) || !isset($array['data'])) {
            $this->fail('Expected a successful answer. Got '.json_encode($array, JSON_PRETTY_PRINT));
        }
        return $array['data'];
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
        ], $this->getSuccessResult($result));

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
        ], $this->getSuccessResult($result));
    }

    public function testDeprecatedField(): void
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
                uppercaseName
                deprecatedUppercaseName
                deprecatedName
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
                    'uppercaseName' => 'JOE',
                    'deprecatedUppercaseName' => 'JOE',
                    'deprecatedName' => 'Joe',
                ],
                [
                    'name' => 'Bill',
                    'uppercaseName' => 'BILL',
                    'deprecatedUppercaseName' => 'BILL',
                    'deprecatedName' => 'Bill',
                ]

            ]
        ], $this->getSuccessResult($result));

        // Let's introspect to see if the field is marked as deprecated
        // in the resulting GraphQL schema
        $queryString = '
            query deprecatedField {
              __type(name: "Contact") {
                fields(includeDeprecated: true) {
                  name
                  isDeprecated
                  deprecationReason
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

        $fields = $this->getSuccessResult($result)['__type']['fields'];
        $deprecatedFields = [
            'deprecatedUppercaseName',
            'deprecatedName'
        ];
        $fields = array_filter($fields, function($field) use ($deprecatedFields) {
            if (in_array($field['name'], $deprecatedFields)) {
                return true;
            }
            return false;
        });
        $this->assertCount(
            count($deprecatedFields),
            $fields,
            'Missing deprecated fields on GraphQL Schema'
        );
        foreach ($fields as $field) {
            $this->assertTrue(
                $field['isDeprecated'],
                'Field ' . $field['name'] . ' must be marked deprecated, but is not'
            );
            $this->assertStringContainsString(
                'use field ',
                $field['deprecationReason'],
                'Field ' . $field['name'] . ' is misssing a deprecation reason'
            );
        }
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
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndInputTypeDate()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation {
          saveBirthDate(birthDate: "1942-12-24T00:00:00+00:00")  {
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
        ], $this->getSuccessResult($result));
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
                "birthDate" => "1942-12-24T00:00:00+00:00"
            ]
        );

        $this->assertSame([
            'saveBirthDate' => [
                'name' => 'Bill',
                'birthDate' => '1942-12-24T00:00:00+00:00',
            ]
        ], $this->getSuccessResult($result));
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
                birthDate: "1942-12-24T00:00:00+00:00",
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));

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
        ], $this->getSuccessResult($result));

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

        $this->assertSame('In the items field of a result set, you cannot add a "offset" without also adding a "limit"', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);


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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));

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
        ], $this->getSuccessResult($result));

        // Call again to test GlobTypeMapper cache
        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoFilters' => [ "foo", "bar", "12", "42", "62" ]
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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

        $this->assertSame('You need to be logged to access this field', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            contacts {
                name
                forLogged
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('You need to be logged to access this field', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

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

        $this->assertSame('You do not have sufficient rights to access this field', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            contacts {
                withRight
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('You do not have sufficient rights to access this field', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            contacts {
                name
                hidden
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Cannot query field "hidden" on type "ContactInterface".', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
    }

    public function testEndToEndEnums2(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            echoSomeProductType
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoSomeProductType' => 'FOOD'
        ], $this->getSuccessResult($result));
    }

    public function testEndToEndEnums3(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query echo($productType: ProductTypes!) {
            echoProductType(productType: $productType)
        }
        ';

        $variables = [
            'productType' => 'NON_FOOD'
        ];

        $result = GraphQL::executeQuery(
            $schema,
            $queryString,
            null,
            null,
            $variables
        );

        $this->assertSame([
            'echoProductType' => 'NON_FOOD'
        ], $this->getSuccessResult($result));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testEndToEndNativeEnums(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $gql = '
            query {
                button(color: red, size: M, state: Off) {
                    color
                    size
                    state
                }
            }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $gql
        );

        $this->assertSame([
            'button' => [
                'color' => 'red',
                'size' => 'M',
                'state' => 'Off',
            ]
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));
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
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
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
        ], $this->getSuccessResult($result));
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
        ], $this->getSuccessResult($result));

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
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
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
        ], $this->getSuccessResult($result));

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
        ], $this->getSuccessResult($result));

        // Test with @FailWith annotation on property
        $queryString = '
        query {
            contacts {
              failWithNull
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $data = $this->getSuccessResult($result);
        $this->assertSame(null, $data['contacts'][0]['failWithNull']);
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

        $this->assertSame('Access denied.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
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

        $this->assertSame('you can see this secret only if user.bar is set to 42', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingUser']);


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

        $this->assertSame('you can see this secret only if user has right "CAN_EDIT"', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingIsGranted']);
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

        $this->assertSame('Access denied.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            secretUsingThis(secret:"42")
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('you can see this secret only if isAllowed() returns true', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['data']['secretUsingThis']);
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

        $this->assertSame('Access denied.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);

        $queryString = '
        query {
            contacts {
                secured
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Access denied.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
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
        $resultArray = $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS);

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
        $resultArray = $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS);

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
        ], $this->getSuccessResult($result));
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

        $this->assertSame(42, $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['data']['injectedUser']);
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

    public function testNullableResult(){
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            nullableResult {
                count
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );
        $resultArray = $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS);
        if (isset($resultArray['errors']) || !isset($resultArray['data'])) {
            $this->fail('Expected a successful answer. Got '.json_encode($resultArray, JSON_PRETTY_PRINT));
        }
        $this->assertNull($resultArray['data']['nullableResult']);
    }

    public function testEndToEndFieldAnnotationInProperty(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
        query {
            contacts {
                age
                nickName
                status
                address
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $data = $this->getSuccessResult($result);

        $this->assertSame(42, $data['contacts'][0]['age']);
        $this->assertSame('foo', $data['contacts'][0]['nickName']);
        $this->assertSame('bar', $data['contacts'][0]['status']);
        $this->assertSame('foo', $data['contacts'][0]['address']);

        $queryString = '
        query {
            contacts {
                private
            }
        }
        ';

        GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(AccessPropertyException::class);
        $this->expectExceptionMessage("Could not get value from property 'TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact::private'. Either make the property public or add a public getter for it like 'getPrivate' or 'isPrivate' with no required parameters");

        $queryString = '
        query {
            contacts {
                zipcode
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(AccessPropertyException::class);
        $this->expectExceptionMessage("Could not get value from property 'TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact::zipcode'. Either make the property public or add a public getter for it like 'getZipcode' or 'isZipcode' with no required parameters");
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndInputAnnotations(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation {
            createPost(
                post: {
                    title: "foo",
                    publishedAt: "2021-01-24T00:00:00+00:00"
                    author: {
                      name: "foo",
                      birthDate: "1942-12-24T00:00:00+00:00",
                      relations: [
                        {
                            name: "bar"
                        }
                      ]
                    }
                }
            ) {
                id
                title
                publishedAt
                comment
                summary
                author {
                  name
                }
            }
            updatePost(
                id: 100,
                post: {
                    title: "bar"
                }
            ) {
                id
                title
                comment
                summary
            }
            createArticle(
                article: {
                    title: "foo",
                    comment: "some description",
                    magazine: "bar",
                    author: {
                      name: "foo",
                      birthDate: "1942-12-24T00:00:00+00:00",
                      relations: [
                        {
                            name: "bar"
                        }
                      ]
                    }
                }
            ) {
                id
                title
                comment
                summary
                magazine
                author {
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
            'createPost' => [
                'id' => 1,
                'title' => 'foo',
                'publishedAt' => '2021-01-24T00:00:00+00:00',
                'comment' => 'foo',
                'summary' => 'foo',
                'author' => [
                    'name' => 'foo',
                ],
            ],
            'updatePost' => [
                'id' => 100,
                'title' => 'bar',
                'comment' => 'bar',
                'summary' => 'foo',
            ],
            'createArticle' => [
                'id' => 2,
                'title' => 'foo',
                'comment' => 'some description',
                'summary' => 'foo',
                'magazine' => 'bar',
                'author' => [
                    'name' => 'foo',
                ],
            ],
        ], $this->getSuccessResult($result));
    }

    public function testEndToEndInputAnnotationIssues(): void
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        mutation {
            createPost(
                post: {
                    id: 20,
                }
            ) {
                id
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Field PostInput.title of required type String! was not provided.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
        $this->assertSame('Field PostInput.publishedAt of required type DateTime! was not provided.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][1]['message']);
        $this->assertSame('Field "id" is not defined by type PostInput.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][2]['message']);

        $queryString = '
        mutation {
            createArticle(
                article: {
                    id: 20,
                    publishedAt: "2021-01-24T00:00:00+00:00"
                }
            ) {
                id
                publishedAt
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame('Field ArticleInput.title of required type String! was not provided.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][0]['message']);
        $this->assertSame('Field "id" is not defined by type ArticleInput.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][1]['message']);
        $this->assertSame('Field "publishedAt" is not defined by type ArticleInput.', $result->toArray(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)['errors'][2]['message']);

        $queryString = '
        mutation {
            updatePost(
                id: 100,
                post: {
                    title: "foo",
                    inaccessible: "foo"
                }
            ) {
                id
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->expectException(AccessPropertyException::class);
        $this->expectExceptionMessage("Could not set value for property 'TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post::inaccessible'. Either make the property public or add a public setter for it like this: 'setInaccessible'");
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testEndToEndInputTypeValidation(): void
    {
        $validator = new Validator();

        $container = $this->createContainer([
            InputTypeGenerator::class => function (ContainerInterface $container) use ($validator) {
                return new InputTypeGenerator(
                    $container->get(InputTypeUtils::class),
                    $container->get(FieldsBuilder::class),
                    $validator
                );
            },
        ]);

        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $schemaFactory = new SchemaFactory(new Psr16Cache($arrayAdapter), new BasicAutoWiringContainer(new EmptyContainer()));
        $schemaFactory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $schemaFactory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models');
        $schemaFactory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types');
        $schemaFactory->setAuthenticationService($container->get(AuthenticationServiceInterface::class));
        $schemaFactory->setAuthorizationService($container->get(AuthorizationServiceInterface::class));
        $schemaFactory->setInputTypeValidator($validator);

        $schema = $schemaFactory->createSchema();

        // Test any mutation, we just need a trigger an InputType to be resolved
        $queryString = '
            mutation {
                createArticle(
                    article: {
                        title: "Old Man and the Sea"
                    }
                ) {
                    title
                }
            }
        ';

        $this->expectException(ValidationException::class);
        $result = GraphQL::executeQuery($schema, $queryString);
        $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
    }

    public function testMuatationNamespaces(): void
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $schemaFactory = new SchemaFactory(new Psr16Cache($arrayAdapter), new BasicAutoWiringContainer(new EmptyContainer()));
        $schemaFactory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\MutationNamespaces\\Controllers');
        $schemaFactory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types');

        $schema = $schemaFactory->createSchema();
//        $schemaExport = SchemaPrinter::doPrint($schema);
        $errors = $schema->validate();
        $this->assertSame([], $errors);
        $this->assertSame("UserMutations",$schema->getMutationType()->getField("user")->getType()->name);
        $this->assertCount(3, $schema->getMutationType()->getField("user")->getType()->config["fields"]());
        $this->assertSame("CustomNS",$schema->getMutationType()->getField("rename")->getType()->name);
        $this->assertCount(3, $schema->getMutationType()->getField("rename")->getType()->config["fields"]());
    }
}
