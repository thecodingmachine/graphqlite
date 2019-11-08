<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use function is_array;
use function iterator_to_array;

class CompositeRootTypeMapper implements RootTypeMapperInterface
{
    /** @var RootTypeMapperInterface[] */
    private $rootTypeMappers;

    /**
     * @param RootTypeMapperInterface[] $rootTypeMappers
     */
    public function __construct(iterable $rootTypeMappers = [])
    {
        $this->rootTypeMappers = is_array($rootTypeMappers) ? $rootTypeMappers : iterator_to_array($rootTypeMappers);
    }

    public function addRootTypeMapper(RootTypeMapperInterface $rootTypeMapper): void
    {
        $this->rootTypeMappers[] = $rootTypeMapper;
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

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): ?NamedType
    {
        foreach ($this->rootTypeMappers as $rootTypeMapper) {
            $mappedType = $rootTypeMapper->mapNameToType($typeName);
            if ($mappedType !== null) {
                return $mappedType;
            }
        }

        return null;
    }
}
