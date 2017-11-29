<?php


namespace TheCodingMachine\GraphQL\Controllers\Registry;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

/**
 * The role of the registry is to provide access to all GraphQL types.
 * If the type is not found, it can be queried from the container, or if not in the container, it can be created from the Registry itself.
 */
class Registry implements ContainerInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AbstractObjectType[]
     */
    private $values = [];
    /**
     * @var null|AuthorizationServiceInterface
     */
    private $authorizationService;

    /**
     * @param ContainerInterface $container The proxied container.
     * @param AuthorizationServiceInterface|null $authorizationService
     */
    public function __construct(ContainerInterface $container, AuthorizationServiceInterface $authorizationService = null)
    {
        $this->container = $container;
        $this->authorizationService = $authorizationService;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (isset($this->values[$id])) {
            return $this->values[$id];
        }
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        if (is_a($id, AbstractObjectType::class, true)) {
            $this->values[$id] = new $id($this);
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
     * @return bool
     */
    public function has($id)
    {
        if (isset($this->values[$id])) {
            return true;
        }
        if ($this->container->has($id)) {
            return true;
        }

        if (is_a($id, AbstractObjectType::class, true)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the authorization service.
     *
     * @return AuthorizationServiceInterface|null
     */
    public function getAuthorizationService(): ?AuthorizationServiceInterface
    {
        return $this->authorizationService;
    }
}
