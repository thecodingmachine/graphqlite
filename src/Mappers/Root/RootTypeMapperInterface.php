<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
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
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType;

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType;
}
