<?php


namespace TheCodingMachine\GraphQL\Controllers;


use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;

class ControllerQueryProviderFactory
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;
    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;
    /**
     * @var ContainerInterface
     */
    private $registry;
    /**
     * @var CachedDocBlockFactory
     */
    private $cachedDocBlockFactory;

    public function __construct(AnnotationReader $annotationReader,
                                HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService,
                                AuthorizationServiceInterface $authorizationService, ContainerInterface $registry,
                                CachedDocBlockFactory $cachedDocBlockFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->registry = $registry;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
    }

    /**
     * @param RecursiveTypeMapperInterface $typeMapper
     * @return ControllerQueryProvider
     */
    public function buildQueryProvider(RecursiveTypeMapperInterface $typeMapper): ControllerQueryProvider
    {
        return new ControllerQueryProvider(
            $this->annotationReader,
            $typeMapper,
            $this->hydrator,
            $this->authenticationService,
            $this->authorizationService,
            $this->registry,
            $this->cachedDocBlockFactory
        );
    }
}
