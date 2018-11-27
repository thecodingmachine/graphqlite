<?php

namespace TheCodingMachine\GraphQL\Controllers\Registry;

use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\AnnotationReader;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;


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
     * @return AnnotationReader
     */
    public function getAnnotationReader(): AnnotationReader;

    /**
     * @return RecursiveTypeMapperInterface
     */
    public function getTypeMapper(): RecursiveTypeMapperInterface;

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface;
}
