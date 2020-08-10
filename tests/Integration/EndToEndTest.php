<?php

namespace TheCodingMachine\GraphQLite\Integration;

use function class_exists;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Exception;
use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\InputType;
use Mouf\Picotainer\Picotainer;
use PhpParser\Comment\Doc;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Type;
use function print_r;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\FieldsBuilderFactory;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Hydrators\FactoryHydrator;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
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
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RecursiveTypeMapperInterface::class), $container->get(TypeResolver::class));
            },
            QueryProviderInterface::class => function(ContainerInterface $container) {
                return new GlobControllerQueryProvider('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers', $container->get(FieldsBuilderFactory::class),
                    $container->get(RecursiveTypeMapperInterface::class), $container->get(BasicAutoWiringContainer::class), $container->get(LockFactory::class), new ArrayCache());
            },
            FieldsBuilderFactory::class => function(ContainerInterface $container) {
                return new FieldsBuilderFactory(
                    $container->get(AnnotationReader::class),
                    $container->get(HydratorInterface::class),
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                    $container->get(TypeResolver::class),
                    $container->get(CachedDocBlockFactory::class),
                    $container->get(NamingStrategyInterface::class)
                );
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
                return new CompositeTypeMapper([
                    $container->get(GlobTypeMapper::class),
                    $container->get(GlobTypeMapper::class.'2'),
                    $container->get(PorpaginasTypeMapper::class),
                ]);
            },
            GlobTypeMapper::class => function(ContainerInterface $container) {
                return new GlobTypeMapper('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Types',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(InputTypeUtils::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(LockFactory::class),
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
                    $container->get(LockFactory::class),
                    new ArrayCache()
                );
            },
            PorpaginasTypeMapper::class => function() {
                return new PorpaginasTypeMapper();
            },
            TypeGenerator::class => function(ContainerInterface $container) {
                return new TypeGenerator(
                    $container->get(AnnotationReader::class),
                    $container->get(FieldsBuilderFactory::class),
                    $container->get(NamingStrategyInterface::class),
                    $container->get(TypeRegistry::class),
                    $container->get(BasicAutoWiringContainer::class)
                );
            },
            TypeRegistry::class => function() {
                return new TypeRegistry();
            },
            InputTypeGenerator::class => function(ContainerInterface $container) {
                return new InputTypeGenerator(
                    $container->get(InputTypeUtils::class),
                    $container->get(FieldsBuilderFactory::class),
                    $container->get(ArgumentResolver::class)
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
            HydratorInterface::class => function(ContainerInterface $container) {
                return new FactoryHydrator();
            },
            ArgumentResolver::class => function(ContainerInterface $container) {
                return new ArgumentResolver($container->get(HydratorInterface::class));
            },
            NamingStrategyInterface::class => function() {
                return new NamingStrategy();
            },
            CachedDocBlockFactory::class => function() {
                return new CachedDocBlockFactory(new ArrayCache());
            },
            LockFactory::class => function() {
                if (extension_loaded('sysvsem')) {
                    $lockStore = new SemaphoreStore();
                } else {
                    $lockStore = new FlockStore(sys_get_temp_dir());
                }
                return new LockFactory($lockStore);
            }
        ]);

        $this->mainContainer->get(TypeResolver::class)->registerSchema($this->mainContainer->get(Schema::class));
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
                    'uppercaseName' => 'JOE'
                ],
                [
                    'name' => 'Bill',
                    'company' => 'Bill Ltd',
                    'uppercaseName' => 'BILL',
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
                    'uppercaseName' => 'JOE'
                ],
                [
                    'name' => 'Bill',
                    'company' => 'Bill Ltd',
                    'uppercaseName' => 'BILL',
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
                        birthDate: "1942-12-24 00:00:00",
                    }
                ]
            }
          ) {
            name,
            birthDate,
            relations {
              name,
              birthDate
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
                        'birthDate' => '1942-12-24T00:00:00+00:00'
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
            echoFilters(filter: {values: ["foo", "bar"]})
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $this->assertSame([
            'echoFilters' => [ "foo", "bar" ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

    public function testDefaultValueInSchema()
    {
        /** @var Schema $schema */
        $schema = $this->mainContainer->get(Schema::class);

        $queryString = '
            query deprecatedField {
              __type(name: "Query") {
                fields {
                  name
                  args {
                    name
                    defaultValue
                  }
                }
              }
            }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        $defaultField = null;
        foreach ($result->data['__type']['fields'] as $field) {
            if ($field['name'] === 'defaultValue') {
                $defaultField = $field;
                break;
            }
        }

        $this->assertSame(
            $defaultField['args'][0]['defaultValue'],
            '"value"'
        );
    }

}
