<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;

/**
 * A factory that creates an AggregateControllerQueryProvider.
 */
class AggregateControllerQueryProviderFactory implements QueryProviderFactoryInterface
{
    /**
     * @param iterable<string> $controllers A list of controllers name in the container.
     * @param ContainerInterface $controllersContainer The container we will fetch controllers from.
     */
    public function __construct(private readonly iterable $controllers, private readonly ContainerInterface $controllersContainer)
    {
    }

    public function create(FactoryContext $context): QueryProviderInterface
    {
        return new AggregateControllerQueryProvider($this->controllers, $context->getFieldsBuilder(), $this->controllersContainer);
    }
}
