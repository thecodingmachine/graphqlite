<?php

namespace TheCodingMachine\GraphQLite\Integration;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\NonNull;
use Mouf\Picotainer\Picotainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\CompositeParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeMismatchException;
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

    public function setUp()
    {
        $this->mainContainer = new Picotainer([
            Schema::class => function(ContainerInterface $container) {
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RecursiveTypeMapperInterface::class), $container->get(TypeResolver::class), null, $container->get(RootTypeMapperInterface::class));
            },
            QueryProviderInterface::class => function(ContainerInterface $container) {
                return new GlobControllerQueryProvider('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers', $container->get(FieldsBuilder::class),
                    $container->get(BasicAutoWiringContainer::class), new ArrayCache());
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
                    $container->get(ParameterMapperInterface::class),
                    $container->get(FieldMiddlewareInterface::class)
                );
            },
            FieldMiddlewareInterface::class => function(ContainerInterface $container) {
                $pipe = new FieldMiddlewarePipe();
                $pipe->pipe($container->get(AuthorizationFieldMiddleware::class));
                return $pipe;
            },
            AuthorizationFieldMiddleware::class => function(ContainerInterface $container) {
                return new AuthorizationFieldMiddleware(
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
                return new RecursiveTypeMapper(
                    $container->get(TypeMapperInterface::class),
                    $container->get(NamingStrategyInterface::class),
                    new ArrayCache(),
                    $container->get(TypeRegistry::class)
                );
            },
            TypeMapperInterface::class => function(ContainerInterface $container) {
                return new CompositeTypeMapper();
            },
            GlobTypeMapper::class => function(ContainerInterface $container) {
                return new GlobTypeMapper('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    new ArrayCache()
                    );
            },
            GlobTypeMapper::class.'2' => function(ContainerInterface $container) {
                return new GlobTypeMapper('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Models',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(RecursiveTypeMapperInterface::class),
                    new ArrayCache()
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
                return new CachedDocBlockFactory(new ArrayCache());
            },
            RootTypeMapperInterface::class => function(ContainerInterface $container) {
                return new CompositeRootTypeMapper([
                    new MyCLabsEnumTypeMapper(),
                    new BaseTypeMapper($container->get(RecursiveTypeMapperInterface::class))
                ]);
            },
            ContainerParameterMapper::class => function(ContainerInterface $container) {
                return new ContainerParameterMapper($container, true, true);
            },
            'testService' => function() {
                return 'foo';
            },
            stdClass::class => function() {
                // Empty test service for autowiring
                return new stdClass();
            },
            ParameterMapperInterface::class => function(ContainerInterface $container) {
                return new CompositeParameterMapper([
                    new ResolveInfoParameterMapper(),
                    $container->get(ContainerParameterMapper::class)
                ]);
            }
        ]);
        $this->mainContainer->get(TypeResolver::class)->registerSchema($this->mainContainer->get(Schema::class));
        $this->mainContainer->get(TypeMapperInterface::class)->addTypeMapper($this->mainContainer->get(GlobTypeMapper::class));
        $this->mainContainer->get(TypeMapperInterface::class)->addTypeMapper($this->mainContainer->get(GlobTypeMapper::class.'2'));
        $this->mainContainer->get(TypeMapperInterface::class)->addTypeMapper($this->mainContainer->get(PorpaginasTypeMapper::class));
    }

    public function testEndToEnd()
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
            $queryString
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

    public function testEndToEndPorpaginas()
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

    public function testEndToEndPorpaginasOnScalarType()
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
    public function testEndToEnd2Iterators()
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

    public function testEndToEndStaticFactories()
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

    public function testNonNullableTypesWithOptionnalFactoryArguments()
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

    public function testNullableTypesWithOptionnalFactoryArguments()
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

    public function testEndToEndResolveInfo()
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

    public function testEndToEndRightIssues()
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

    public function testAutowireService()
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

        $this->expectException(TypeMismatchException::class);
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
}
