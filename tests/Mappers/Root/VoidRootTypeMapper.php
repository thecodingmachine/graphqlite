<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;

class VoidRootTypeMapper implements RootTypeMapperInterface
{
    /**
     * @var RootTypeMapperInterface
     */
    private $next;

    public function __construct(RootTypeMapperInterface $next)
    {
        $this->next = $next;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
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
        return $this->next->mapNameToType($typeName);
    }
}
