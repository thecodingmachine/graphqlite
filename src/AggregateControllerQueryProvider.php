<?php


namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

/**
 * A query provider that looks into all controllers of your application to fetch queries.
 */
class AggregateControllerQueryProvider implements QueryProviderInterface
{
    /**
     * @var array|string[]
     */
    private $controllers;
    /**
     * @var ContainerInterface
     */
    private $controllersContainer;
    /**
     * @var FieldsBuilderFactory
     */
    private $queryProviderFactory;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;

    /**
     * @param string[] $controllers A list of controllers name in the container.
     * @param FieldsBuilderFactory $queryProviderFactory
     * @param ContainerInterface $controllersContainer The container we will fetch controllers from.
     */
    public function __construct(iterable $controllers, FieldsBuilderFactory $queryProviderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, ContainerInterface $controllersContainer)
    {
        $this->controllers = $controllers;
        $this->queryProviderFactory = $queryProviderFactory;
        $this->controllersContainer = $controllersContainer;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        $queryList = [];

        foreach ($this->controllers as $controllerName) {
            $controller = $this->controllersContainer->get($controllerName);
            $queryProvider = $this->queryProviderFactory->buildFieldsBuilder($this->recursiveTypeMapper);
            $queryList = array_merge($queryList, $queryProvider->getQueries($controller));
        }

        return $queryList;
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        $mutationList = [];

        foreach ($this->controllers as $controllerName) {
            $controller = $this->controllersContainer->get($controllerName);
            $queryProvider = $this->queryProviderFactory->buildFieldsBuilder($this->recursiveTypeMapper);
            $mutationList = array_merge($mutationList, $queryProvider->getMutations($controller));
        }

        return $mutationList;
    }
}
