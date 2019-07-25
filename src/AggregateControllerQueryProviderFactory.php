<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;

/**
 * A factory that creates an AggregateControllerQueryProvider.
 */
class AggregateControllerQueryProviderFactory implements QueryProviderFactoryInterface
{
    /** @var array|string[] */
    private $controllers;
    /** @var ContainerInterface */
    private $controllersContainer;

    /**
     * @param string[]           $controllers          A list of controllers name in the container.
     * @param ContainerInterface $controllersContainer The container we will fetch controllers from.
     */
    public function __construct(iterable $controllers, ContainerInterface $controllersContainer)
    {
        $this->controllers          = $controllers;
        $this->controllersContainer = $controllersContainer;
    }

    public function create(FactoryContext $context): QueryProviderInterface
    {
        return new AggregateControllerQueryProvider($this->controllers, $context->getFieldsBuilder(), $this->controllersContainer);
    }
}
