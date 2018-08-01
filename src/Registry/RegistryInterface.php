<?php

namespace TheCodingMachine\GraphQL\Controllers\Registry;

use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;


/**
 * The role of the registry is to provide access to all GraphQL types.
 * If the type is not found, it can be queried from the container, or if not in the container, it can be created from the Registry itself.
 */
interface RegistryInterface extends ContainerInterface
{
    /**
     * Returns the authorization service.
     *
     * @return AuthorizationServiceInterface
     */
    public function getAuthorizationService(): AuthorizationServiceInterface;

    /**
     * @return AuthenticationServiceInterface
     */
    public function getAuthenticationService(): AuthenticationServiceInterface;

    /**
     * @return Reader
     */
    public function getAnnotationReader(): Reader;

    /**
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface;

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface;
}