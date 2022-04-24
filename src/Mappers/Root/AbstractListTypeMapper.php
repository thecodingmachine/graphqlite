<?php
declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Closure;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use ReflectionMethod;
use ReflectionProperty;
use Webmozart\Assert\Assert;

use function assert;

class AbstractListTypeMapper implements RootTypeMapperInterface
{
    /** @var RootTypeMapperInterface */
    private $topRootTypeMapper;
    /** @var RootTypeMapperInterface */
    private $next;

    public function __construct(RootTypeMapperInterface $next, RootTypeMapperInterface $topRootTypeMapper)
    {
        $this->topRootTypeMapper = $topRootTypeMapper;
        $this->next = $next;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     * @param ReflectionMethod|ReflectionProperty $reflector
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType
    {
        if (! $type instanceof AbstractList) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        $result = $this->toGraphQLType($type,
            function (Type $type, ?OutputType $subType) use ($reflector, $docBlockObj) {
                return $this->topRootTypeMapper->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
            });

        if ($result === null) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }
        Assert::isInstanceOf($result, OutputType::class);

        return $result;
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     * @param ReflectionMethod|ReflectionProperty $reflector
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType
    {
        // ToDo: what (and how) should we do here about collections?
        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
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

    /**
     * @param AbstractList $type
     *
     * @return (OutputType&GraphQLType)|(InputType&GraphQLType)
     */
    private function toGraphQLType(AbstractList $type, Closure $topToGraphQLType)
    {
        $singleDocBlockType = $type->getValueType();

        $subGraphQlType = $topToGraphQLType($singleDocBlockType, null);
        $graphQlType = $topToGraphQLType($singleDocBlockType, $subGraphQlType);

        return new ListOfType($graphQlType);
    }
}
