<?php

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQLite\Fixtures\TestController;

class GlobControllerQueryProviderTest extends AbstractQueryProviderTest
{
    public function testGlob()
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

        $globControllerQueryProvider = new GlobControllerQueryProvider('TheCodingMachine\\GraphQLite', $this->getControllerQueryProviderFactory(), $this->getTypeMapper(), $container, new NullCache());

        $queries = $globControllerQueryProvider->getQueries();
        $this->assertCount(6, $queries);

        $mutations = $globControllerQueryProvider->getMutations();
        $this->assertCount(1, $mutations);

    }
}
