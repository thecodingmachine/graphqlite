<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Containers;

use GraphQL\Type\Definition\ObjectType;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use function class_exists;

/**
 * The BasicAutoWiringContainer is a container wrapper that will automatically instantiate classes that have
 * no constructor arguments.
 */
class BasicAutoWiringContainer implements ContainerInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var ObjectType[] */
    private $values = [];

    /**
     * @param ContainerInterface $container The proxied container.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function get($id)
    {
        if (isset($this->values[$id])) {
            return $this->values[$id];
        }
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        // The container will try to instantiate the type if the class exists and has an annotation.
        if (class_exists($id)) {
            $refTypeClass = new ReflectionClass($id);
            if ($refTypeClass->hasMethod('__construct') && $refTypeClass->getMethod('__construct')->getNumberOfRequiredParameters() > 0) {
                throw NotFoundException::notFoundInContainer($id);
            }
            $this->values[$id] = new $id();

            return $this->values[$id];
        }

        throw NotFoundException::notFound($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function has($id): bool
    {
        if (isset($this->values[$id])) {
            return true;
        }
        if ($this->container->has($id)) {
            return true;
        }

        if (class_exists($id)) {
            $refTypeClass = new ReflectionClass($id);

            return $refTypeClass->isInstantiable() && ! ($refTypeClass->hasMethod('__construct') && $refTypeClass->getMethod('__construct')->getNumberOfRequiredParameters() > 0);
        }

        return false;
    }
}
