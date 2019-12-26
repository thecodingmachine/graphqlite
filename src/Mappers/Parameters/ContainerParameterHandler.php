<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Container\ContainerInterface;
use ReflectionNamedType;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\ContainerParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use function assert;

/**
 * Maps parameters with the \@Autowire annotation to container entry based on the FQCN or the passed identifier.
 */
class ContainerParameterHandler implements ParameterMiddlewareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        $autowire = $parameterAnnotations->getAnnotationByType(Autowire::class);

        if ($autowire === null) {
            return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        $id = $autowire->getIdentifier();
        if ($id === null) {
            $type = $parameter->getType();
            if ($type === null) {
                throw MissingAutowireTypeException::create($parameter);
            }
            assert($type instanceof ReflectionNamedType);
            $id = $type->getName();
        }

        return new ContainerParameter($this->container, $id);
    }
}
