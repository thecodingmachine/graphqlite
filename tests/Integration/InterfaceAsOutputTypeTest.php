<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;

/**
 * A #[Type] on a PHP interface (with a custom name and a single concrete implementer) that is used
 * as an output type used to break schema building: getOutputTypes() called the object-only
 * mapClassToType() on the interface class once it was already registered, throwing
 * "Expected GraphQL type ... to be an MutableObjectType".
 */
class InterfaceAsOutputTypeTest extends TestCase
{
    private Schema $schema;

    public function setUp(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());

        $schemaFactory = new SchemaFactory(new Psr16Cache(new ArrayAdapter()), $container);
        $schemaFactory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\InterfaceAsOutputType');

        $this->schema = $schemaFactory->createSchema();
    }

    public function testSchemaBuildsWhenInterfaceIsUsedAsOutputType(): void
    {
        // Resolve the interface by name first (as a name-based outputType reference does), so it is
        // already registered by the time the full type map / getOutputTypes() runs.
        $this->schema->getType('Product');

        $this->schema->assertValid();

        $this->assertNotNull($this->schema->getType('Product'));
        $this->assertNotNull($this->schema->getType('Book'));
    }

    public function testQueryReturningInterfaceResolvesConcreteType(): void
    {
        $result = GraphQL::executeQuery(
            $this->schema,
            '
            query {
                product {
                    name
                    ... on Book {
                        author
                    }
                }
            }
            ',
        );

        $this->assertSame(
            [
                'product' => [
                    'name' => 'The Trial',
                    'author' => 'Franz Kafka',
                ],
            ],
            $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS)['data']
                ?? $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS)['errors'],
        );
    }
}
