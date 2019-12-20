<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function iterator_to_array;

/**
 * This root type mapper is the very first type mapper that must be called.
 * It handles the "compound" types and is in charge of creating Union Types and detecting subTypes (for arrays)
 */
class NullableTypeMapperAdapter implements RootTypeMapperInterface
{
    /** @var RootTypeMapperInterface */
    private $next;

    public function setNext(RootTypeMapperInterface $next): void
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
        // Let's check a "null" value in the docblock
        $isNullable = $this->isNullable($type);

        if ($isNullable) {
            $nonNullableType = $this->getNonNullable($type);
            if ($nonNullableType === null) {
                throw CannotMapTypeException::createForNull();
            }
            $type = $nonNullableType;
        }

        $graphQlType = $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);

        if (! $isNullable && $graphQlType instanceof NullableType) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }

        return $graphQlType;
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        // Let's check a "null" value in the docblock
        $isNullable = $this->isNullable($type);

        if ($isNullable) {
            $nonNullableType = $this->getNonNullable($type);
            if ($nonNullableType === null) {
                throw CannotMapTypeException::createForNull();
            }
            $type = $nonNullableType;
        }

        $graphQlType = $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);

        // The type is non nullable if the PHP argument is non nullable
        // There is an exception: if the PHP argument is non nullable but points to a factory that can called without passing any argument,
        // then, the input type is nullable (and we can still create an empty object).
        if (! $isNullable && $graphQlType instanceof NullableType) {
            if (! ($graphQlType instanceof ResolvableMutableInputObjectType) || $graphQlType->isInstantiableWithoutParameters() !== true) {
                $graphQlType = GraphQLType::nonNull($graphQlType);
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
    public function mapNameToType(string $typeName): NamedType
    {
        return $this->next->mapNameToType($typeName);
    }

    private function isNullable(Type $docBlockTypeHint): bool
    {
        if ($docBlockTypeHint instanceof Null_ || $docBlockTypeHint instanceof Nullable) {
            return true;
        }
        if ($docBlockTypeHint instanceof Compound) {
            foreach ($docBlockTypeHint as $type) {
                if ($this->isNullable($type)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getNonNullable(Type $type): ?Type
    {
        if ($type instanceof Null_) {
            return null;
        }
        if ($type instanceof Nullable) {
            return $type->getActualType();
        }
        if ($type instanceof Compound) {
            $types = array_map([$this, 'getNonNullable'], iterator_to_array($type));
            // Remove null values
            $types = array_values(array_filter($types));
            if (count($types) > 1) {
                return new Compound($types);
            }

            return $types[0] ?? null;
        }

        return $type;
    }
}
