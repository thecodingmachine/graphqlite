<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\DuplicateMappingException;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\TypeHandler;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidRootTypeMapperFactory;
use TheCodingMachine\GraphQLite\Mappers\StaticClassListTypeMapperFactory;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Fixtures\TestSelfType;


class SchemaFactoryTest extends TestCase
{

    public function testCreateSchema(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

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
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);

        $factory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->setDoctrineAnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader())
                ->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setNamingStrategy(new NamingStrategy())
                ->addTypeMapper(new CompositeTypeMapper())
                ->addTypeMapperFactory(new StaticClassListTypeMapperFactory([TestSelfType::class]))
                ->addRootTypeMapperFactory(new VoidRootTypeMapperFactory())
                ->addParameterMiddleware(new ParameterMiddlewarePipe())
                ->addQueryProviderFactory(new AggregateControllerQueryProviderFactory([], $container))
                ->setSchemaConfig(new SchemaConfig())
                ->setExpressionLanguage(new ExpressionLanguage(new Psr16Adapter(new Psr16Cache(new ArrayAdapter()))))
                ->devMode()
                ->prodMode();

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testClassNameMapperInjectionWithValidMapper(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
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
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer()
            )
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setClassNameMapper(new ClassNameMapper())
                ->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers')
                ->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(CannotMapTypeException::class);
        $this->doTestSchema($factory->createSchema());
    }

    public function testException(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);

        $this->expectException(GraphQLRuntimeException::class);
        $factory->createSchema();
    }

    public function testException2(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(GraphQLRuntimeException::class);
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

    public function testDuplicateQueryException(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer()
            )
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueries')
                ->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(DuplicateMappingException::class);
        $this->expectExceptionMessage("The query/mutation/field 'duplicateQuery' is declared twice in class 'TheCodingMachine\GraphQLite\Fixtures\DuplicateQueries\TestControllerWithDuplicateQuery'. First in 'TheCodingMachine\GraphQLite\Fixtures\DuplicateQueries\TestControllerWithDuplicateQuery::testDuplicateQuery1()', second in 'TheCodingMachine\GraphQLite\Fixtures\DuplicateQueries\TestControllerWithDuplicateQuery::testDuplicateQuery2()'");
        $schema = $factory->createSchema();
        $queryString = '
        query {
            duplicateQuery
        }
        ';
        GraphQL::executeQuery(
            $schema,
            $queryString
        );
    }

    public function testDuplicateQueryInTwoControllersException(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer()
            )
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
            ->setAuthorizationService(new VoidAuthorizationService())
            ->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueriesInTwoControllers')
            ->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->expectException(DuplicateMappingException::class);
        $this->expectExceptionMessage("The query/mutation 'duplicateQuery' is declared twice: in class 'TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueriesInTwoControllers\\TestControllerWithDuplicateQuery1' and in class 'TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueriesInTwoControllers\\TestControllerWithDuplicateQuery2");
        $schema = $factory->createSchema();
        $queryString = '
        query {
            duplicateQuery
        }
        ';
        GraphQL::executeQuery(
            $schema,
            $queryString
        );
    }
}
