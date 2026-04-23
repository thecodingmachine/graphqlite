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
 * Regression test for issue #308: MutableInterfaceType not frozen when used
 * exclusively as a mutation return type.
 *
 * @see https://github.com/thecodingmachine/graphqlite/issues/308
 */
class MutationInterfaceFreezeTest extends TestCase
{
    private Schema $schema;

    public function setUp(): void
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());

        $schemaFactory = new SchemaFactory(new Psr16Cache(new ArrayAdapter()), $container);
        $schemaFactory->addNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\MutationInterfaceFreeze');

        $this->schema = $schemaFactory->createSchema();
    }

    /**
     * Schema validation triggers getTypeMap() which traverses all types.
     * Before the fix, this would throw:
     * "You must freeze() a MutableObjectType before fetching its fields."
     */
    public function testSchemaValidationDoesNotThrowForMutationOnlyInterface(): void
    {
        $this->schema->assertValid();
        $this->addToAssertionCount(1);
    }

    /**
     * Executing a mutation that returns an interface type should work
     * without any query also returning that interface.
     */
    public function testMutationReturningInterfaceCanBeExecuted(): void
    {
        $queryString = '
        mutation {
            mutateResult {
                message
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString,
        );

        $this->assertSame(
            ['mutateResult' => ['message' => 'success']],
            $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS)['data'],
        );
    }

    /**
     * Inline fragments on an interface type in a mutation should work.
     * Before the fix, the OverlappingFieldsCanBeMerged validator would
     * access the unfrozen MutableInterfaceType and crash.
     */
    public function testMutationWithInlineFragmentOnInterface(): void
    {
        $queryString = '
        mutation {
            mutateResult {
                ... on ResultInterface {
                    message
                }
                ... on ConcreteResult {
                    message
                    extra
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString,
        );

        $this->assertSame(
            [
                'mutateResult' => [
                    'message' => 'success',
                    'extra' => 'extra',
                ],
            ],
            $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS)['data'],
        );
    }

    /**
     * Introspection must include the mutation return interface type
     * and it must be resolvable without errors.
     */
    public function testIntrospectionIncludesMutationInterfaceType(): void
    {
        $queryString = '
        {
            __type(name: "ResultInterface") {
                kind
                name
                fields {
                    name
                }
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $this->schema,
            $queryString,
        );

        $data = $result->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS)['data'];
        $this->assertSame('INTERFACE', $data['__type']['kind']);
        $this->assertSame('ResultInterface', $data['__type']['name']);
        $this->assertContains(
            ['name' => 'message'],
            $data['__type']['fields'],
        );
    }
}
