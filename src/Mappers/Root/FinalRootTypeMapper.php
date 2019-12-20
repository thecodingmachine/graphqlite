<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

/**
 * The final root type mapper of the RootTypeMapperInterface chain.
 * If we reach this root type mapper, it means we could not find a GraphQL type for the PHP type and we must
 * throw an exception.
 * In the case of "mapNameToType", the mapping is delegated to the recursive type mapper.
 */
final class FinalRootTypeMapper implements RootTypeMapperInterface
{
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;

    public function __construct(RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        throw CannotMapTypeException::createForPhpDocType($type);
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        throw CannotMapTypeException::createForPhpDocType($type);
    }

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): NamedType
    {
        return $this->recursiveTypeMapper->mapNameToType($typeName);
    }
}
