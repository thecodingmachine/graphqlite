<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters;


use GraphQL\Type\Definition\ResolveInfo;
use phpDocumentor\Reflection\DocBlock;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ResolveInfoParameter;

class ResolveInfoParameterMapper implements ParameterMapperInterface
{
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, array $paramTags): ?ParameterInterface
    {
        $type = $parameter->getType();
        if ($type!== null && $type->getName() === ResolveInfo::class) {
            return new ResolveInfoParameter();
        }
        return null;
    }
}
