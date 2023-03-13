<?php

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TheCodingMachine\GraphQLite\Fixtures\TestController;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

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
    }
}
