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
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $identifier;

    public function __construct(ContainerInterface $container, string $identifier)
    {
        $this->container = $container;
        $this->identifier = $identifier;
    }

    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $this->container->get($this->identifier);
    }
}
