<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use function is_array;
use function iterator_to_array;

class CompositeParameterMapper implements ParameterMapperInterface
{
    /** @var ParameterMapperInterface[] */
    private $parameterMappers;

    /**
     * @param ParameterMapperInterface[] $parameterMappers
     */
    public function __construct(iterable $parameterMappers)
    {
        $this->parameterMappers = is_array($parameterMappers) ? $parameterMappers : iterator_to_array($parameterMappers);
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations): ?ParameterInterface
    {
        foreach ($this->parameterMappers as $parameterMapper) {
            $parameterObj = $parameterMapper->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
            if ($parameterObj !== null) {
                return $parameterObj;
            }
        }

        return null;
    }
}
