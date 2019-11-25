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

/**
 * Maps a method return type or argument to a GraphQL Type.
 *
 * Unlike TypeMapperInterface that maps a class to a GraphQL object, RootTypeMapperInterface has access to
 * the "context" (i.e. the function signature, the annotations...). Also, it can map to any types (not only objects,
 * but also scalar types...)
 *
 * We call it "RootTypeMapper" because it is the first type mapper to be called. It will call the recursive type
 * mappers which will in turn, call the "type mappers".
 */
interface RootTypeMapperInterface
{
    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType;

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType;

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): NamedType;
}
