<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;
use Psr\Container\ContainerInterface;

/**
 * A parameter filled from the container.
 */
class ContainerParameter implements ParameterInterface
{
    public function __construct(private ContainerInterface $container, private string $identifier)
    {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $this->container->get($this->identifier);
    }
}
