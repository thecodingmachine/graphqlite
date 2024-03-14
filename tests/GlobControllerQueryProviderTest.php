<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Kcs\ClassFinder\Finder\ComposerFinder;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Fixtures\TestController;

class GlobControllerQueryProviderTest extends AbstractQueryProviderTest
{
    public function testGlob(): void
    {
        $controller = new TestController();

        $container = new class ([TestController::class => $controller]) implements ContainerInterface {
            /** @var array */
            private $controllers;

            public function __construct(array $controllers)
            {
                $this->controllers = $controllers;
            }

            public function get($id): mixed
            {
                return $this->controllers[$id];
            }

            public function has($id): bool
            {
                return isset($this->controllers[$id]);
            }
        };

        $finder = new ComposerFinder();
        $finder->filter(static fn (ReflectionClass $class) => $class->getNamespaceName() === 'TheCodingMachine\\GraphQLite\\Fixtures'); // Fix for recursive:false
        $globControllerQueryProvider = new GlobControllerQueryProvider(
            'TheCodingMachine\\GraphQLite\\Fixtures',
            $this->getFieldsBuilder(),
            $container,
            $this->getAnnotationReader(),
            new Psr16Cache(new NullAdapter()),
            $finder,
            0,
        );

        $queries = $globControllerQueryProvider->getQueries();
        $this->assertCount(9, $queries);

        $mutations = $globControllerQueryProvider->getMutations();
        $this->assertCount(2, $mutations);

        $subscriptions = $globControllerQueryProvider->getSubscriptions();
        $this->assertCount(2, $subscriptions);
    }
}
