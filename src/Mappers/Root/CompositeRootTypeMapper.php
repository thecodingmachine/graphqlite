<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use function is_array;
use function iterator_to_array;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;

class CompositeRootTypeMapper implements RootTypeMapperInterface
{
    /**
     * @var RootTypeMapperInterface[]
     */
    private $rootTypeMappers;

    /**
     * @param RootTypeMapperInterface[] $rootTypeMappers
     */
    public function __construct(iterable $rootTypeMappers)
    {
        $this->rootTypeMappers = is_array($rootTypeMappers) ? $rootTypeMappers : iterator_to_array($rootTypeMappers);
    }

    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
    {
        foreach ($this->rootTypeMappers as $rootTypeMapper) {
            $mappedType = $rootTypeMapper->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
            if ($mappedType !== null) {
                return $mappedType;
            }
        }
        return null;
    }

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
    {
        foreach ($this->rootTypeMappers as $rootTypeMapper) {
            $mappedType = $rootTypeMapper->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
            if ($mappedType !== null) {
                return $mappedType;
            }
        }
        return null;
    }
}
