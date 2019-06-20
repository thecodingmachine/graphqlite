<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Parameters\ContainerParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

/**
 * Tries to map parameters with FQCN type hints to a container entry with the FQCN or the parameter name.
 */
class ContainerParameterMapper implements ParameterMapperInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var bool */
    private $mapFullyQualifiedClassName;
    /** @var bool */
    private $mapParameterName;

    public function __construct(ContainerInterface $container, bool $mapFullyQualifiedClassName, bool $mapParameterName)
    {
        $this->container = $container;
        $this->mapFullyQualifiedClassName = $mapFullyQualifiedClassName;
        $this->mapParameterName = $mapParameterName;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ?Parameter $parameterAnnotation): ?ParameterInterface
    {
        if ($this->mapParameterName) {
            $parameterName = $parameter->getName();
            if ($this->container->has($parameterName)) {
                return new ContainerParameter($this->container, $parameterName);
            }
        }
        if ($this->mapFullyQualifiedClassName) {
            $type = $parameter->getType();
            if ($type !== null && $this->container->has($type->getName())) {
                return new ContainerParameter($this->container, $type->getName());
            }
        }

        return null;
    }
}
