<?php


namespace TheCodingMachine\GraphQL\Controllers\Registry;

use function class_exists;
use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TheCodingMachine\GraphQL\Controllers\AnnotationUtils;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

/**
 * The role of the registry is to provide access to all GraphQL types.
 * If the type is not found, it can be queried from the container, or if not in the container, it can be created from the Registry itself.
 */
class Registry implements RegistryInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ObjectType[]
     */
    private $values = [];
    /**
     * @var null|AuthorizationServiceInterface
     */
    private $authorizationService;
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;
    /**
     * @var Reader
     */
    private $annotationReader;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $typeMapper;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @param ContainerInterface $container The proxied container.
     */
    public function __construct(ContainerInterface $container, AuthorizationServiceInterface $authorizationService, AuthenticationServiceInterface $authenticationService, Reader $annotationReader, RecursiveTypeMapperInterface $typeMapper, HydratorInterface $hydrator)
    {
        $this->container = $container;
        $this->authorizationService = $authorizationService;
        $this->authenticationService = $authenticationService;
        $this->annotationReader = $annotationReader;
        $this->typeMapper = $typeMapper;
        $this->hydrator = $hydrator;
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

        // The registry will try to instantiate the type if the class exists and has an annotation.
        if ($this->isGraphqlType($id)) {
            $refTypeClass = new \ReflectionClass($id);
            if ($refTypeClass->hasMethod('__construct') && $refTypeClass->getMethod('__construct')->getNumberOfRequiredParameters() > 0) {
                throw NotFoundException::notFoundInContainer($id);
            }
            // FIXME: TypeGenerator is hard coded here
            $typeGenerator = new TypeGenerator($this);
            $this->values[$id] = $typeGenerator->mapAnnotatedObject(new $id());
            return $this->values[$id];
        }


        throw NotFoundException::notFound($id);
    }

    private function isGraphqlType(string $className) : bool
    {
        if (!class_exists($className))  {
            return false;
        }
        $refTypeClass = new \ReflectionClass($className);

        /** @var \TheCodingMachine\GraphQL\Controllers\Annotations\Type|null $typeField */
        $typeField = AnnotationUtils::getClassAnnotation($this->getAnnotationReader(), $refTypeClass, \TheCodingMachine\GraphQL\Controllers\Annotations\Type::class);
        return $typeField !== null;
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

        /*if (is_a($id, ObjectType::class, true)) {
            return true;
        }*/
        return $this->isGraphqlType($id);
    }

    /**
     * Returns the authorization service.
     *
     * @return AuthorizationServiceInterface
     */
    public function getAuthorizationService(): AuthorizationServiceInterface
    {
        return $this->authorizationService;
    }

    /**
     * @return AuthenticationServiceInterface
     */
    public function getAuthenticationService(): AuthenticationServiceInterface
    {
        return $this->authenticationService;
    }

    /**
     * @return Reader
     */
    public function getAnnotationReader(): Reader
    {
        return $this->annotationReader;
    }

    /**
     * @return RecursiveTypeMapperInterface
     */
    public function getTypeMapper(): RecursiveTypeMapperInterface
    {
        return $this->typeMapper;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }
}
