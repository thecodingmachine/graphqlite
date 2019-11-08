<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\TypeMappingRuntimeException;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\UnionType;
use Webmozart\Assert\Assert;
use function array_filter;
use function iterator_to_array;

/**
 * This root type mapper is used when one of the types (in a compound type) is an iterator.
 * In this case, if the other types are arrays, they are passed as subTypes. For instance: ResultIterator|User[] => ResultIterator<User>
 */
class IteratorTypeMapper implements RootTypeMapperInterface
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
        $iteratorType = null;
        $key = null;
        $types = iterator_to_array($type);
        foreach ($types as $key => $singleDocBlockType) {
            if (! ($singleDocBlockType instanceof Object_)) {
                continue;
            }

            $fqcn     = (string) $singleDocBlockType->getFqsen();
            $refClass = new ReflectionClass($fqcn);
            // Note : $refClass->isIterable() is only accessible in PHP 7.2
            if (! $refClass->implementsInterface(Iterator::class) && ! $refClass->implementsInterface(IteratorAggregate::class)) {
                continue;
            }
            $iteratorType = $singleDocBlockType;
            break;
        }

        if ($iteratorType === null) {
            return null;
        }

        // One of the classes in the compound is an iterator. Let's remove it from the list and let's test all other values as potential subTypes.
        unset($types[$key]);

        $unionTypes = [];
        $lastException = null;
        foreach ($types as $singleDocBlockType) {
            try {
                $singleDocBlockType = $this->getTypeInArray($singleDocBlockType);
                if ($singleDocBlockType !== null) {
                    $subGraphQlType = $this->topRootTypeMapper->toGraphQLOutputType($singleDocBlockType, null, $refMethod, $docBlockObj);
                    //$subGraphQlType = $this->toGraphQlType($singleDocBlockType, null, false, $refMethod, $docBlockObj);

                    // By convention, we trim the NonNull part of the "$subGraphQlType"
                    if ($subGraphQlType instanceof NonNull) {
                        $subGraphQlType = $subGraphQlType->getWrappedType();
                    }
                } else {
                    $subGraphQlType = null;
                }

                $unionTypes[] = $this->topRootTypeMapper->toGraphQLOutputType($iteratorType, $subGraphQlType, $refMethod, $docBlockObj);

                // TODO: add here a scan of the $type variable and do stuff if it is iterable.
                // TODO: remove the iterator type if specified in the docblock (@return Iterator|User[])
                // TODO: check there is at least one array (User[])
            } catch (TypeMappingRuntimeException | CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;

                if ($singleDocBlockType !== null) {
                    // The type is an array (like User[]). Let's use that.
                    $valueType = $this->topRootTypeMapper->toGraphQLOutputType($singleDocBlockType, null, $refMethod, $docBlockObj);
                    if ($valueType !== null) {
                        $unionTypes[] = new ListOfType($valueType);
                    }
                }
            }
        }

        if (empty($unionTypes) && $lastException !== null) {
            // We have an issue, let's try without the subType
            try {
                $result = $this->topRootTypeMapper->toGraphQLOutputType($iteratorType, null, $refMethod, $docBlockObj);
            } catch (TypeMappingRuntimeException | CannotMapTypeExceptionInterface $otherException) {
                // Still an issue? Let's rethrow the previous exception.
                throw $lastException;
            }
            return $result;
            //return $this->mapDocBlockType($type, $docBlockType, $isNullable, false, $refMethod, $docBlockObj);
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            $graphQlType = new UnionType($unionTypes, $this->recursiveTypeMapper);
            $graphQlType = $this->typeRegistry->getOrRegisterType($graphQlType);
        }

        return $graphQlType;
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
        $iteratorType = null;
        $key = null;
        $types = iterator_to_array($type);
        foreach ($types as $key => $singleDocBlockType) {
            if (! ($singleDocBlockType instanceof Object_)) {
                continue;
            }

            $fqcn     = (string) $singleDocBlockType->getFqsen();
            $refClass = new ReflectionClass($fqcn);
            // Note : $refClass->isIterable() is only accessible in PHP 7.2
            if (! $refClass->implementsInterface(Iterator::class) && ! $refClass->implementsInterface(IteratorAggregate::class)) {
                continue;
            }
            $iteratorType = $singleDocBlockType;
            break;
        }

        if ($iteratorType === null) {
            return null;
        }

        // One of the classes in the compound is an iterator. Let's remove it from the list and let's test all other values as potential subTypes.
        unset($types[$key]);

        $unionTypes = [];
        $lastException = null;
        foreach ($types as $singleDocBlockType) {
            try {
                $singleDocBlockType = $this->getTypeInArray($singleDocBlockType);
                if ($singleDocBlockType !== null) {
                    $subGraphQlType = $this->topRootTypeMapper->toGraphQLOutputType($singleDocBlockType, null, $refMethod, $docBlockObj);
                    //$subGraphQlType = $this->toGraphQlType($singleDocBlockType, null, false, $refMethod, $docBlockObj);
                } else {
                    $subGraphQlType = null;
                }

                $unionTypes[] = $this->topRootTypeMapper->toGraphQLInputType($iteratorType, $subGraphQlType, $argumentName, $refMethod, $docBlockObj);

                // TODO: add here a scan of the $type variable and do stuff if it is iterable.
                // TODO: remove the iterator type if specified in the docblock (@return Iterator|User[])
                // TODO: check there is at least one array (User[])
            } catch (TypeMappingRuntimeException | CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;

                if ($singleDocBlockType !== null) {
                    // The type is an array (like User[]). Let's use that.
                    $unionTypes[] = $this->topRootTypeMapper->toGraphQLInputType($singleDocBlockType, null, $argumentName, $refMethod, $docBlockObj);
                }
            }
        }

        if (empty($unionTypes) && $lastException !== null) {
            // We have an issue, let's try without the subType
            try {
                $result = $this->topRootTypeMapper->toGraphQLInputType($iteratorType, null, $argumentName, $refMethod, $docBlockObj);
            } catch (TypeMappingRuntimeException | CannotMapTypeExceptionInterface $otherException) {
                // Still an issue? Let's rethrow the previous exception.
                throw $lastException;
            }
            return $result;
            //return $this->mapDocBlockType($type, $docBlockType, $isNullable, false, $refMethod, $docBlockObj);
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            // There are no union input types. Something went wrong.
            return null;
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
        // TODO: how to handle this? Do we need?
        return null;
    }


    /**
     * Resolves a list type.
     */
    private function getTypeInArray(Type $typeHint): ?Type
    {
        if (! $typeHint instanceof Array_) {
            return null;
        }

        return $this->dropNullableType($typeHint->getValueType());
    }

    /**
     * Drops "Nullable" types and return the core type.
     */
    private function dropNullableType(Type $typeHint): Type
    {
        if ($typeHint instanceof Nullable) {
            return $typeHint->getActualType();
        }

        return $typeHint;
    }
}
