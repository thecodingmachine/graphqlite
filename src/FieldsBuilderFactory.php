<?php


namespace TheCodingMachine\GraphQL\Controllers;


use TheCodingMachine\GraphQL\Controllers\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Types\TypeResolver;

class FieldsBuilderFactory
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
     * @var CachedDocBlockFactory
     */
    private $cachedDocBlockFactory;
    /**
     * @var TypeResolver
     */
    private $typeResolver;
    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    public function __construct(AnnotationReader $annotationReader,
                                HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService,
                                AuthorizationServiceInterface $authorizationService, TypeResolver $typeResolver,
                                CachedDocBlockFactory $cachedDocBlockFactory, NamingStrategyInterface $namingStrategy)
    {
        $this->annotationReader = $annotationReader;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->typeResolver = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param RecursiveTypeMapperInterface $typeMapper
     * @return FieldsBuilder
     */
    public function buildFieldsBuilder(RecursiveTypeMapperInterface $typeMapper): FieldsBuilder
    {
        return new FieldsBuilder(
            $this->annotationReader,
            $typeMapper,
            $this->hydrator,
            $this->authenticationService,
            $this->authorizationService,
            $this->typeResolver,
            $this->cachedDocBlockFactory,
            $this->namingStrategy
        );
    }
}
