<?php


namespace TheCodingMachine\GraphQL\Controllers;

use phpDocumentor\Reflection\Types\Mixed;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use Youshido\GraphQL\Field\Field;
use Youshido\GraphQL\Field\FieldInterface;

/**
 * A query provider that looks into all controllers of your application to fetch queries.
 */
class AggregateControllerQueryProvider implements QueryProviderInterface
{
    /**
     * @var array|\string[]
     */
    private $controllers;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param string[] $controllers A list of controllers name in the container.
     * @param RegistryInterface $registry
     * @param ContainerInterface|null $container The container we will fetch controllers from. If not specified, container from the registry is used instead.
     */
    public function __construct(array $controllers, RegistryInterface $registry, ContainerInterface $container = null)
    {
        $this->controllers = $controllers;
        $this->registry = $registry;
        $this->container = $container ?: $registry;
    }

    /**
     * @return FieldInterface[]
     */
    public function getQueries(): array
    {
        $queryList = [];

        foreach ($this->controllers as $controllerName) {
            $controller = $this->container->get($controllerName);
            $queryProvider = new ControllerQueryProvider($controller, $this->registry);
            $queryList = array_merge($queryList, $queryProvider->getQueries());
        }

        return $queryList;
    }

    /**
     * @return FieldInterface[]
     */
    public function getMutations(): array
    {
        $mutationList = [];

        foreach ($this->controllers as $controllerName) {
            $controller = $this->container->get($controllerName);
            $queryProvider = new ControllerQueryProvider($controller, $this->registry);
            $mutationList = array_merge($mutationList, $queryProvider->getMutations());
        }

        return $mutationList;
    }
}
