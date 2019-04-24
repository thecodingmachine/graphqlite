<?php


namespace TheCodingMachine\GraphQLite;


use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

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
    /**
     * @var RootTypeMapperInterface[]
     */
    private $rootTypeMappers;

    /**
     * @param RootTypeMapperInterface[] $rootTypeMappers
     */
    public function __construct(AnnotationReader $annotationReader,
                                HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService,
                                AuthorizationServiceInterface $authorizationService, TypeResolver $typeResolver,
                                CachedDocBlockFactory $cachedDocBlockFactory, NamingStrategyInterface $namingStrategy,
                                array $rootTypeMappers = [])
    {
        $this->annotationReader = $annotationReader;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->typeResolver = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy = $namingStrategy;
        $this->rootTypeMappers = $rootTypeMappers;
    }

    /**
     * @param RecursiveTypeMapperInterface $typeMapper
     * @return FieldsBuilder
     */
    public function buildFieldsBuilder(RecursiveTypeMapperInterface $typeMapper): FieldsBuilder
    {
        $rootTypeMappers = $this->rootTypeMappers;
        $rootTypeMappers[] = new BaseTypeMapper($typeMapper);
        return new FieldsBuilder(
            $this->annotationReader,
            $typeMapper,
            new ArgumentResolver($this->hydrator),
            $this->authenticationService,
            $this->authorizationService,
            $this->typeResolver,
            $this->cachedDocBlockFactory,
            $this->namingStrategy,
            new CompositeRootTypeMapper($rootTypeMappers)
        );
    }
}
