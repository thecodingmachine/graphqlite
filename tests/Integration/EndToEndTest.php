<?php

namespace TheCodingMachine\GraphQL\Controllers\Integration;

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
use TheCodingMachine\GraphQL\Controllers\AnnotationReader;
use TheCodingMachine\GraphQL\Controllers\ControllerQueryProviderFactory;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQL\Controllers\GlobControllerQueryProvider;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\InputTypeGenerator;
use TheCodingMachine\GraphQL\Controllers\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\NamingStrategy;
use TheCodingMachine\GraphQL\Controllers\NamingStrategyInterface;
use TheCodingMachine\GraphQL\Controllers\QueryProviderInterface;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Containers\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Schema;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

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
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RecursiveTypeMapperInterface::class));
            },
            QueryProviderInterface::class => function(ContainerInterface $container) {
                return new GlobControllerQueryProvider('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Integration\\Controllers', $container->get(ControllerQueryProviderFactory::class),
                    $container->get(RecursiveTypeMapperInterface::class), $container->get(BasicAutoWiringContainer::class), new ArrayCache());
            },
            ControllerQueryProviderFactory::class => function(ContainerInterface $container) {
                return new ControllerQueryProviderFactory(
                    $container->get(AnnotationReader::class),
                    $container->get(HydratorInterface::class),
                    $container->get(AuthenticationServiceInterface::class),
                    $container->get(AuthorizationServiceInterface::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(CachedDocBlockFactory::class)
                );
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
                return new RecursiveTypeMapper($container->get(TypeMapperInterface::class), $container->get(NamingStrategyInterface::class), new ArrayCache());
            },
            TypeMapperInterface::class => function(ContainerInterface $container) {
                return new GlobTypeMapper('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Integration\\Types',
                    $container->get(TypeGenerator::class),
                    $container->get(InputTypeGenerator::class),
                    $container->get(BasicAutoWiringContainer::class),
                    $container->get(AnnotationReader::class),
                    $container->get(NamingStrategyInterface::class),
                    new ArrayCache()
                    );
            },
            TypeGenerator::class => function(ContainerInterface $container) {
                return new TypeGenerator(
                    $container->get(AnnotationReader::class),
                    $container->get(ControllerQueryProviderFactory::class),
                    $container->get(NamingStrategyInterface::class)
                );
            },
            InputTypeGenerator::class => function(ContainerInterface $container) {
                return new InputTypeGenerator(
                    $container->get(AnnotationReader::class),
                    $container->get(ControllerQueryProviderFactory::class),
                    $container->get(NamingStrategyInterface::class)
                );
            },
            AnnotationReader::class => function(ContainerInterface $container) {
                return new AnnotationReader(new DoctrineAnnotationReader());
            },
            HydratorInterface::class => function(ContainerInterface $container) {
                return new class implements HydratorInterface
                {
                    public function hydrate(array $data, InputType $type)
                    {
                        return new Contact($data['name']);
                    }
                };
            },
            NamingStrategyInterface::class => function() {
                return new NamingStrategy();
            },
            CachedDocBlockFactory::class => function() {
                return new CachedDocBlockFactory(new ArrayCache());
            }
        ]);
    }

    public function testEndToEnd()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        query {
            getContacts {
                name
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
            'getContacts' => [
                [
                    'name' => 'Joe'
                ],
                [
                    'name' => 'Bill',
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
            'getContacts' => [
                [
                    'name' => 'Joe'
                ],
                [
                    'name' => 'Bill',
                    'email' => 'bill@example.com'
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }

}
