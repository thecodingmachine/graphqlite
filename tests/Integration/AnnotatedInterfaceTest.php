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
use TheCodingMachine\GraphQLite\SchemaFactory;
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

class AnnotatedInterfaceTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    public function setUp(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());

        $schemaFactory = new SchemaFactory(new ArrayCache(), $container);
        $schemaFactory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\AnnotatedInterfaces\\Controllers');
        $schemaFactory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\AnnotatedInterfaces\\Types');

        $this->schema = $schemaFactory->createSchema();
    }

    public function testClassA(): void
    {
        $this->schema->assertValid();

        $queryString = '
        query {
            classA {
                foo
                bar
                parentValue
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString
        );

        $this->assertSame([
            'classA' => [
                'foo' => 'foo',
                'bar' => 'bar',
                'parentValue' => 'parent',
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data'] ?? $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['errors']);
    }

    public function testAnnotatedInterfaceWithNotAnnotatedClass(): void
    {
        $queryString = '
        query {
            qux {
                qux
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString
        );

        $this->assertSame([
            'qux' => [
                'qux' => 'qux',
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data'] ?? $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['errors']);
    }

    public function testAnnotatedInterfaceWithAnnotatedClass(): void
    {
        $queryString = '
        query {
            classDAsWizInterface {
                wizz
                ... on ClassD {
                    foo
                    bar
                    parentValue
                    classD
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString
        );

        $this->assertSame([
            'classDAsWizInterface' => [
                'wizz' => 'wizz',
                'foo' => 'foo',
                'bar' => 'bar',
                'parentValue' => 'parent',
                'classD' => 'classD',
            ]
        ], $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['data'] ?? $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS)['errors']);
    }

}
