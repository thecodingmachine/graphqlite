<?php

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQLite\Fixtures\TestController;

class GlobControllerQueryProviderTest extends AbstractQueryProviderTest
{
    public function testGlob(): void
    {
        $controller = new TestController();

        $container = new class([ TestController::class => $controller ]) implements ContainerInterface {
            /**
             * @var array
             */
            private $controllers;

            public function __construct(array $controllers)
            {
                $this->controllers = $controllers;
            }

            public function get($id)
            {
                return $this->controllers[$id];
            }

            public function has($id)
            {
                return isset($this->controllers[$id]);
            }
        };

        $globControllerQueryProvider = new GlobControllerQueryProvider('TheCodingMachine\\GraphQLite\\Fixtures', $this->getFieldsBuilder(), $container, $this->getAnnotationReader(), new Psr16Cache(new NullAdapter()), null, false, false);

        $queries = $globControllerQueryProvider->getQueries();
        $this->assertCount(7, $queries);

        $mutations = $globControllerQueryProvider->getMutations();
        $this->assertCount(1, $mutations);

    }
}
