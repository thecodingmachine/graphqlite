<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use function array_merge;

/**
 * A query provider that looks into all controllers of your application to fetch queries.
 */
class AggregateControllerQueryProvider implements QueryProviderInterface
{
    /** @var array|string[] */
    private $controllers;
    /** @var ContainerInterface */
    private $controllersContainer;
    /** @var FieldsBuilder */
    private $fieldsBuilder;

    /**
     * @param string[]           $controllers          A list of controllers name in the container.
     * @param ContainerInterface $controllersContainer The container we will fetch controllers from.
     */
    public function __construct(iterable $controllers, FieldsBuilder $fieldsBuilder, ContainerInterface $controllersContainer)
    {
        $this->controllers          = $controllers;
        $this->fieldsBuilder        = $fieldsBuilder;
        $this->controllersContainer = $controllersContainer;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        $queryList = [];

        foreach ($this->controllers as $controllerName) {
            $controller = $this->controllersContainer->get($controllerName);
            $queryList  = array_merge($queryList, $this->fieldsBuilder->getQueries($controller));
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
            $controller   = $this->controllersContainer->get($controllerName);
            $mutationList = array_merge($mutationList, $this->fieldsBuilder->getMutations($controller));
        }

        return $mutationList;
    }
}
