<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Kcs\ClassFinder\Finder\ComposerFinder;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Discovery\Cache\HardClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\KcsClassFinder;
use TheCodingMachine\GraphQLite\Fixtures\TestController;

use function md5;

class GlobControllerQueryProviderTest extends AbstractQueryProvider
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

        $namespace = 'TheCodingMachine\\GraphQLite\\Fixtures';
        $finder = new ComposerFinder();
        $finder->inNamespace($namespace);
        $finder->filter(static fn (ReflectionClass $class) => $class->getNamespaceName() === $namespace); // Fix for recursive:false
        $hash = md5($namespace);

        $globControllerQueryProvider = new GlobControllerQueryProvider(
            $this->getFieldsBuilder(),
            $container,
            $this->getAnnotationReader(),
            new KcsClassFinder($finder, $hash),
            new HardClassFinderComputedCache(new Psr16Cache(new NullAdapter())),
        );

        $queries = $globControllerQueryProvider->getQueries();
        $this->assertCount(9, $queries);

        $mutations = $globControllerQueryProvider->getMutations();
        $this->assertCount(2, $mutations);

        $subscriptions = $globControllerQueryProvider->getSubscriptions();
        $this->assertCount(2, $subscriptions);
    }
}
