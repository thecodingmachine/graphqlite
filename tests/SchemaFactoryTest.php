<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\SchemaConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\PhpFilesCache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\CompositeParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class SchemaFactoryTest extends TestCase
{

    public function testCreateSchema(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new PhpFilesCache();

        $factory = new SchemaFactory($cache, $container);
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());

        $factory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->addQueryProvider(new AggregateQueryProvider([]));
        $factory->addFieldMiddleware(new FieldMiddlewarePipe());

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testSetters(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new PhpFilesCache();

        $factory = new SchemaFactory($cache, $container);

        $factory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->setDoctrineAnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader())
                ->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setNamingStrategy(new NamingStrategy())
                ->addTypeMapper(new CompositeTypeMapper())
                ->addTypeMapperFactory(new class implements TypeMapperFactoryInterface {
                    public function create(RecursiveTypeMapperInterface $recursiveTypeMapper): TypeMapperInterface
                    {
                        return new CompositeTypeMapper();
                    }
                })
                ->addRootTypeMapper(new CompositeRootTypeMapper([]))
                ->addParameterMapper(new CompositeParameterMapper([]))
                ->setSchemaConfig(new SchemaConfig())
                ->devMode()
                ->prodMode();

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testException(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new PhpFilesCache();

        $factory = new SchemaFactory($cache, $container);

        $this->expectException(GraphQLException::class);
        $factory->createSchema();
    }

    public function testException2(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new PhpFilesCache();

        $factory = new SchemaFactory($cache, $container);
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(GraphQLException::class);
        $factory->createSchema();
    }

    private function doTestSchema(Schema $schema): void
    {

        $schema->assertValid();

        $queryString = '
        query {
            contacts {
                name
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
                    'uppercaseName' => 'JOE'
                ],
                [
                    'name' => 'Bill',
                    'uppercaseName' => 'BILL',
                    'email' => 'bill@example.com'
                ]

            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data']);
    }
}
