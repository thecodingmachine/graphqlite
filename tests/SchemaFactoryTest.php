<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\DebugFlag;
use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Type\SchemaConfig;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Finder\RecursiveFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers\ContactController;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Comment;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Company;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\User;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\CompanyType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ContactFactory;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ContactOtherType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ContactType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ExtendedContactType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\PostType;
use TheCodingMachine\GraphQLite\Fixtures\TestSelfType;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\DuplicateMappingException;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidRootTypeMapperFactory;
use TheCodingMachine\GraphQLite\Mappers\StaticClassListTypeMapperFactory;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class SchemaFactoryTest extends TestCase
{
    public function testCreateSchema(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());

        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->addQueryProvider(new AggregateQueryProvider([]));
        $factory->addFieldMiddleware(new FieldMiddlewarePipe());
        $factory->addInputFieldMiddleware(new InputFieldMiddlewarePipe());

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testSetters(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);

        $factory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->setAuthenticationService(new VoidAuthenticationService())
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

    public function testFinderInjectionWithValidMapper(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer(),
            ),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setFinder(new ComposerFinder())
                ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testCreateSchemaOnlyWithFactories(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());

        $factory->addTypeMapperFactory(new StaticClassListTypeMapperFactory([
            Contact::class,
            ContactFactory::class,
            ContactOtherType::class,
            ContactType::class,
            Comment::class,
            Post::class,
            PostType::class,
            Company::class,
            CompanyType::class,
            ExtendedContactType::class,
            User::class,
        ]));
        $factory->addQueryProviderFactory(new AggregateControllerQueryProviderFactory([ContactController::class], $container));

        $schema = $factory->createSchema();

        $this->doTestSchema($schema);
    }

    public function testFinderInjectionWithInvalidMapper(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer(),
            ),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setFinder(new RecursiveFinder(__DIR__ . '/Annotations'))
                ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $this->doTestSchemaWithError($factory->createSchema());
    }

    public function testException(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);

        $this->expectException(GraphQLRuntimeException::class);
        $factory->createSchema();
    }

    private function execTestQuery(Schema $schema): ExecutionResult
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

        return GraphQL::executeQuery(
            $schema,
            $queryString,
        );
    }

    private function doTestSchemaWithError(Schema $schema): void
    {
        $result = $this->execTestQuery($schema);
        $resultArr = $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
        $this->assertArrayHasKey('errors', $resultArr);
        $this->assertArrayNotHasKey('data', $resultArr);
        $this->assertCount(1, $resultArr);
        $this->assertSame('Unknown type "User"', $resultArr['errors'][0]['message']);
    }

    private function doTestSchema(Schema $schema): void
    {
        $result = $this->execTestQuery($schema);
        $resultArr = $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);
        $this->assertArrayHasKey('data', $resultArr);
        $this->assertSame([
            'contacts' => [
                [
                    'name' => 'Joe',
                    'uppercaseName' => 'JOE',
                ],
                [
                    'name' => 'Bill',
                    'uppercaseName' => 'BILL',
                    'email' => 'bill@example.com',
                ],

            ],
        ], $resultArr['data']);
    }

    public function testDuplicateQueryException(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer(),
            ),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueries')
                ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

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
            $queryString,
        );
    }

    public function testDuplicateQueryInTwoControllersException(): void
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(
                new EmptyContainer(),
            ),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService())
            ->setAuthorizationService(new VoidAuthorizationService())
            ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\DuplicateQueriesInTwoControllers')
            ->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

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
            $queryString,
        );
    }
}
