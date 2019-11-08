<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\TypeMappingRuntimeException;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\UnionType;
use function array_filter;
use function iterator_to_array;

/**
 * This root type mapper is the very first type mapper that must be called.
 * It handles the "compound" types and is in charge of creating Union Types and detecting subTypes (for arrays)
 */
class CompoundTypeMapper implements RootTypeMapperInterface
{
    /**
     * @var RootTypeMapperInterface
     */
    private $topRootTypeMapper;
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;

    public function __construct(RootTypeMapperInterface $topRootTypeMapper, TypeRegistry $typeRegistry, RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->topRootTypeMapper = $topRootTypeMapper;
        $this->typeRegistry = $typeRegistry;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return (OutputType&GraphQLType)|null
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
    {
        if (!$type instanceof Compound) {
            return null;
        }

        $filteredDocBlockTypes = iterator_to_array($type);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingRuntimeException::createFromType($type);
        }

        $unionTypes    = [];
        $lastException = null;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            $unionTypes[] = $this->topRootTypeMapper->toGraphQLOutputType($singleDocBlockType, null, $refMethod, $docBlockObj);
        }

        return $this->getTypeFromUnion($unionTypes);
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return (InputType&GraphQLType)|null
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
    {
        if (!$type instanceof Compound) {
            return null;
        }

        $filteredDocBlockTypes = iterator_to_array($type);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingRuntimeException::createFromType($type);
        }

        $unionTypes    = [];
        $lastException = null;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            $unionTypes[] = $this->topRootTypeMapper->toGraphQLInputType($singleDocBlockType, null, $argumentName, $refMethod, $docBlockObj);
        }

        return $this->getTypeFromUnion($unionTypes);
    }

    /*
     * @template T of InputType|OutputType|null
     * @param array<T> $unionTypes
     * @return T
     */
    private function getTypeFromUnion(array $unionTypes)
    {
        // Remove null values
        $unionTypes = array_values(array_filter($unionTypes));

        $isNullable = false;

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            $badTypes = [];
            $nonNullableUnionTypes = [];
            foreach ($unionTypes as $unionType) {
                if (!$unionType instanceof NonNull) {
                    $isNullable = true;
                } else {
                    $unionType = $unionType->getWrappedType();
                }
                if ($unionType instanceof ObjectType) {
                    $nonNullableUnionTypes[] = $unionType;
                    continue;
                }

                $badTypes[] = $unionType;
            }
            if ($badTypes !== []) {
                // TODO!
                // TODO!
                // TODO!
                // TODO!
                // We need a middleware to handle this case...
                throw CannotMapTypeException::createForBadTypeInUnion($unionTypes);
            }

            $graphQlType = new UnionType($nonNullableUnionTypes, $this->recursiveTypeMapper);
            $graphQlType = $this->typeRegistry->getOrRegisterType($graphQlType);

            if (!$isNullable) {
                $graphQlType = new NonNull($graphQlType);
            }
        }

        return $graphQlType;
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
        // TODO: maybe we should map "Union" types here?
        return null;
    }
}
