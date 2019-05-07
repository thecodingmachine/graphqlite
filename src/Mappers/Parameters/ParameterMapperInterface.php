<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters;


use phpDocumentor\Reflection\DocBlock;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

interface ParameterMapperInterface
{
    /**
     * @param array<string, DocBlock\Tags\Param> $paramTags
     */
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, array $paramTags): ?ParameterInterface;
}
