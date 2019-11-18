<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Closure;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\TypeMappingRuntimeException;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\UnionType;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_values;
use function count;
use function iterator_to_array;

/**
 * This root type mapper is the very first type mapper that must be called.
 * It handles the "compound" types and is in charge of creating Union Types and detecting subTypes (for arrays)
 */
class CompoundTypeMapper implements RootTypeMapperInterface
{
    /** @var RootTypeMapperInterface */
    private $topRootTypeMapper;
    /** @var TypeRegistry */
    private $typeRegistry;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var RootTypeMapperInterface */
    private $next;

    public function __construct(RootTypeMapperInterface $next, RootTypeMapperInterface $topRootTypeMapper, TypeRegistry $typeRegistry, RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->topRootTypeMapper = $topRootTypeMapper;
        $this->typeRegistry = $typeRegistry;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->next = $next;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        if (! $type instanceof Compound) {
            return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }

        $result = $this->toGraphQLType($type, function (Type $type, ?OutputType $subType) use ($refMethod, $docBlockObj) {
            return $this->topRootTypeMapper->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }, true);

        Assert::isInstanceOf($result, OutputType::class);

        return $result;
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        if (! $type instanceof Compound) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }

        $result = $this->toGraphQLType($type, function (Type $type, ?InputType $subType) use ($refMethod, $docBlockObj, $argumentName) {
            return $this->topRootTypeMapper->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }, false);
        Assert::isInstanceOf($result, InputType::class);

        return $result;
    }

    /**
     * @return (OutputType&GraphQLType)|(InputType&GraphQLType)
     */
    private function toGraphQLType(Compound $type, Closure $topToGraphQLType, bool $isOutputType): GraphQLType
    {
        $filteredDocBlockTypes = iterator_to_array($type);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingRuntimeException::createFromType($type);
        }

        $unionTypes    = [];
        $lastException = null;
        $mustBeIterable = false;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            if ($singleDocBlockType instanceof Iterable_) {
                $mustBeIterable = true;
                continue;
            }
            $unionTypes[] = $topToGraphQLType($singleDocBlockType, null);
        }

        if ($mustBeIterable && empty($unionTypes)) {
            throw TypeMappingRuntimeException::createFromType(new Iterable_());
        }

        /** @var OutputType&GraphQLType $return */
        $return = $this->getTypeFromUnion($unionTypes);

        if ($mustBeIterable && ! $this->isWrappedListOfType($return)) {
            // The compound type is iterable and the other type is not iterable. Both types are incompatible
            // For instance: @return iterable|User
            // FIXME: better error message!
            throw TypeMappingRuntimeException::createFromType(new Iterable_());
        }

        if (! $isOutputType && ($return instanceof UnionType || ($return instanceof NonNull && $return->getWrappedType() instanceof UnionType))) {
            throw CannotMapTypeException::createForUnionInInputType($return);
        }

        return $return;
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
     * @throws CannotMapTypeException
     */
    private function getTypeFromUnion(array $unionTypes): GraphQLType
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
                if (! $unionType instanceof NonNull) {
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
            /** @var UnionType $graphQlType */
            $graphQlType = $this->typeRegistry->getOrRegisterType($graphQlType);

            if (! $isNullable) {
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
    public function mapNameToType(string $typeName): NamedType
    {
        return $this->next->mapNameToType($typeName);
    }
}
