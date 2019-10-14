<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\PhpFilesCache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Hydrators\FactoryHydrator;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
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
                ->setHydrator(new FactoryHydrator())
                ->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setNamingStrategy(new NamingStrategy())
                ->addTypeMapper(new CompositeTypeMapper([]))
                ->setSchemaConfig(new SchemaConfig());

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testClassNameMapperInjectionWithValidMapper(): void
    {
        $factory = new SchemaFactory(
            new ArrayCache(),
            new BasicAutoWiringContainer(
                new EmptyContainer()
            )
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setClassNameMapper(ClassNameMapper::createFromComposerFile(null, null, true))
                ->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers')
                ->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testClassNameMapperInjectionWithInvalidMapper(): void
    {
        $factory = new SchemaFactory(
            new ArrayCache(),
            new BasicAutoWiringContainer(
                new EmptyContainer()
            )
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setClassNameMapper(new ClassNameMapper())
                ->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers')
                ->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(\TypeError::class);
        $this->doTestSchema($factory->createSchema());
    }

    public function testException(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new ArrayCache();

        $factory = new SchemaFactory($cache, $container);

        $this->expectException(GraphQLException::class);
        $factory->createSchema();
    }

    public function testException2(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new ArrayCache();

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
