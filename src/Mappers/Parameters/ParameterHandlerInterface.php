<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

interface ParameterHandlerInterface
{
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations): ParameterInterface;
}
