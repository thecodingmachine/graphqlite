<?php

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\Fixtures\TestController;

class AggregateControllerQueryProviderTest extends AbstractQueryProviderTest
{
    public function testAggregate(): void
    {
        $controller = new TestController();

        $container = new class([ 'controller' => $controller ]) implements ContainerInterface {

            public function __construct(private array $controllers)
            {
            }

            public function get($id):mixed
            {
                return $this->controllers[$id];
            }

            public function has($id):bool
            {
                return isset($this->controllers[$id]);
            }
        };

        $aggregateQueryProvider = new AggregateControllerQueryProvider([ 'controller' ], $this->getFieldsBuilder(), $container);

        $queries = $aggregateQueryProvider->getQueries();
        $this->assertCount(9, $queries);

        $mutations = $aggregateQueryProvider->getMutations();
        $this->assertCount(2, $mutations);

        $subscriptions = $aggregateQueryProvider->getSubscriptions();
        $this->assertCount(2, $subscriptions);
    }
}
