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
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

class AnnotatedInterfaceTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    public function setUp(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());

        $schemaFactory = new SchemaFactory(new Psr16Cache(new ArrayAdapter()), $container);
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
                grandFather
                grandMother
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
                'grandFather' => 'grandFather',
                'grandMother' => 'grandMother',
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
