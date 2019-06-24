<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Container\ContainerInterface;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Parameters\ContainerParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

/**
 * Maps parameters with the \@Autowire annotation to container entry based on the FQCN or the passed identifier.
 */
class ContainerParameterMapper implements ParameterMapperInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ?Parameter $parameterAnnotation): ?ParameterInterface
    {
        if ($parameterAnnotation === null) {
            return null;
        }

        /**
         * @var Autowire|null $autowire
         */
        $autowire = $parameterAnnotation->getAnnotationByType(Autowire::class);

        if ($autowire === null) {
            return null;
        }

        $id = $autowire->getIdentifier();
        if ($id === null) {
            $type = $parameter->getType();
            if ($type === null) {
                throw MissingAutowireTypeException::create($parameter);
            }
            $id = $type->getName();
        }

        return new ContainerParameter($this->container, $id);
    }
}
