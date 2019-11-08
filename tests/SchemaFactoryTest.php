<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\SchemaConfig;
use Mouf\Composer\ClassNameMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\PhpFilesCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\TypeHandler;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
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
        $cache = new ArrayCache();

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
        $cache = new ArrayCache();

        $factory = new SchemaFactory($cache, $container);

        $factory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');
        $factory->setDoctrineAnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader())
                ->setAuthenticationService(new VoidAuthenticationService())
                ->setAuthorizationService(new VoidAuthorizationService())
                ->setNamingStrategy(new NamingStrategy())
                ->addTypeMapper(new CompositeTypeMapper())
                ->addTypeMapperFactory(new StaticClassListTypeMapperFactory([TestSelfType::class]))
                ->addRootTypeMapper(new CompositeRootTypeMapper())
                ->addParameterMiddleware(new ParameterMiddlewarePipe())
                ->addQueryProviderFactory(new AggregateControllerQueryProviderFactory([], $container))
                ->setSchemaConfig(new SchemaConfig())
                ->setExpressionLanguage(new ExpressionLanguage(new Psr16Adapter(new ArrayCache())))
                ->devMode()
                ->prodMode();

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

        $this->expectException(CannotMapTypeException::class);
        $this->doTestSchema($factory->createSchema());
    }

    public function testException(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new ArrayCache();

        $factory = new SchemaFactory($cache, $container);

        $this->expectException(GraphQLRuntimeException::class);
        $factory->createSchema();
    }

    public function testException2(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new ArrayCache();

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
}
