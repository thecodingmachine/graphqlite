<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function iterator_to_array;

/**
 * This root type mapper wraps types as "non nullable" if the corresponding PHPDoc type doesn't allow null.
 */
class NullableTypeMapperAdapter implements RootTypeMapperInterface
{
    public function __construct(
        private readonly RootTypeMapperInterface $next,
    )
    {
    }

    public function toGraphQLOutputType(Type $type, OutputType|GraphQLType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
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

        $graphQlType = $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);

        if ($graphQlType instanceof NonNull) {
            throw CannotMapTypeException::createForNonNullReturnByTypeMapper();
        }

        if (! $isNullable && $graphQlType instanceof NullableType) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }

        return $graphQlType;
    }

    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
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

        $graphQlType = $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);

        // The type is non-nullable if the PHP argument is non-nullable
        // There is an exception: if the PHP argument is non-nullable but points to a factory that can called without passing any argument,
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
    public function mapNameToType(string $typeName): NamedType&GraphQLType
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

    private function getNonNullable(Type $type): Type|null
    {
        if ($type instanceof Null_) {
            return null;
        }
        if ($type instanceof Nullable) {
            return $this->getNonNullable($type->getActualType());
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
