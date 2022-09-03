<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\UnionType;

use function array_filter;
use function array_values;
use function assert;
use function count;
use function iterator_to_array;

/**
 * This root type mapper is the very first type mapper that must be called.
 * It handles the "compound" types and is in charge of creating Union Types and detecting subTypes (for arrays)
 */
class CompoundTypeMapper implements RootTypeMapperInterface
{
    public function __construct(private RootTypeMapperInterface $next, private RootTypeMapperInterface $topRootTypeMapper, private TypeRegistry $typeRegistry, private RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
    }

    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType
    {
        if (! $type instanceof Compound) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        $filteredDocBlockTypes = iterator_to_array($type);
        if (! (count($filteredDocBlockTypes) > 0)) {
            throw new InvalidArgumentException();
        }

        $unionTypes    = [];
        $mustBeIterable = false;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            if ($singleDocBlockType instanceof Iterable_) {
                $mustBeIterable = true;
                continue;
            }
            if ($singleDocBlockType instanceof Nullable && $singleDocBlockType->getActualType() instanceof Null_) {
                continue;
            }
            $unionTypes[] = $this->topRootTypeMapper->toGraphQLOutputType($singleDocBlockType, null, $reflector, $docBlockObj);
        }

        if ($mustBeIterable && empty($unionTypes)) {
            throw new RuntimeException('Iterable compound type cannot be alone in the compound.');
        }

        $return = $this->getTypeFromUnion($unionTypes);

        if ($mustBeIterable && ! $this->isWrappedListOfType($return)) {
            // The compound type is iterable and the other type is not iterable. Both types are incompatible
            // For instance: @return iterable|User
            throw CannotMapTypeException::createForBadTypeInUnionWithIterable($return);
        }

        return $return;
    }

    public function toGraphQLInputType(Type $type, null|InputType|GraphQLType $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType|GraphQLType
    {
        if (! $type instanceof Compound) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
        }

        // At this point, the |null has been removed and the |iterable has been removed too.
        // So there should only be compound input types, which is forbidden by the GraphQL spec.
        // Let's kill this right away
        throw CannotMapTypeException::createForInputUnionType($type);
    }

    private function isWrappedListOfType(GraphQLType $type): bool
    {
        if ($type instanceof ListOfType) {
            return true;
        }

        return $type instanceof NonNull && $type->getWrappedType() instanceof ListOfType;
    }

    /*
     * @template T of InputType|OutputType|null
     * @param array<T> $unionTypes
     * @return T
     */

    /**
     * @param array<(InputType&GraphQLType)|(OutputType&GraphQLType)> $unionTypes
     *
     * @return OutputType&GraphQLType
     *
     * @throws CannotMapTypeException
     */
    private function getTypeFromUnion(array $unionTypes): GraphQLType
    {
        // Remove null values
        $unionTypes = array_values(array_filter($unionTypes));

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
            assert($graphQlType instanceof NonNull);
            // If we have only one type, let's make it nullable (it is the role of the NullableTypeMapperAdapter to make it non nullable)
            $graphQlType = $graphQlType->getWrappedType();
            assert($graphQlType instanceof OutputType);
        } else {
            $badTypes = [];
            $nonNullableUnionTypes = [];
            foreach ($unionTypes as $unionType) {
                // We are sure that each $unionType is not nullable (because nullable types have been filtered in the NullableTypeMapperAdapter already)
                assert($unionType instanceof NonNull);
                $unionType = $unionType->getWrappedType();
                if ($unionType instanceof ObjectType) {
                    $nonNullableUnionTypes[] = $unionType;
                    continue;
                }

                $badTypes[] = $unionType;
            }
            if ($badTypes !== []) {
                throw CannotMapTypeException::createForBadTypeInUnion($unionTypes);
            }

            $graphQlType = new UnionType($nonNullableUnionTypes, $this->recursiveTypeMapper);
            $graphQlType = $this->typeRegistry->getOrRegisterType($graphQlType);
            assert($graphQlType instanceof UnionType);
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
    public function mapNameToType(string $typeName): NamedType
    {
        return $this->next->mapNameToType($typeName);
    }
}
