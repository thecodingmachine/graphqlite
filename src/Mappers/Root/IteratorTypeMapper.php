<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Closure;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use Webmozart\Assert\Assert;
use function assert;
use function count;
use function iterator_to_array;

/**
 * This root type mapper is used when one of the types (in a compound type) is an iterator.
 * In this case, if the other types are arrays, they are passed as subTypes. For instance: ResultIterator|User[] => ResultIterator<User>
 */
class IteratorTypeMapper implements RootTypeMapperInterface
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
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        if (! $type instanceof Compound) {
            try {
                return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
            } catch (CannotMapTypeException $e) {
                if ($type instanceof Object_) {
                    $fqcn = (string) $type->getFqsen();
                    $refClass = new ReflectionClass($fqcn);
                    // Note : $refClass->isIterable() is only accessible in PHP 7.2
                    if ($refClass->isIterable()) {
                        throw CannotMapTypeException::createForMissingIteratorValue($fqcn, $e);
                    }
                }
                throw $e;
            }
        }

        $result = $this->toGraphQLType($type, function (Type $type, ?OutputType $subType) use ($refMethod, $docBlockObj) {
            return $this->topRootTypeMapper->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }, true);

        if ($result === null) {
            return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }
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
            //try {
                return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);

            /*} catch (CannotMapTypeException $e) {
                $this->throwIterableMissingTypeHintException($e, $type);
            }*/
        }

        $result = $this->toGraphQLType($type, function (Type $type, ?InputType $subType) use ($refMethod, $docBlockObj, $argumentName) {
            return $this->topRootTypeMapper->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }, false);
        if ($result === null) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }
        Assert::isInstanceOf($result, InputType::class);

        return $result;
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
        // TODO: how to handle this? Do we need?
        return $this->next->mapNameToType($typeName);
    }

    /**
     * Resolves a list type.
     */
    private function getTypeInArray(Type $typeHint): ?Type
    {
        if (! $typeHint instanceof Array_) {
            return null;
        }

        return $typeHint->getValueType();
    }

    /**
     * @param Compound<Type> $type
     *
     * @return (OutputType&GraphQLType)|(InputType&GraphQLType)|null
     */
    private function toGraphQLType(Compound $type, Closure $topToGraphQLType, bool $isOutputType)
    {
        $types = iterator_to_array($type);

        $iteratorType = $this->splitIteratorFromOtherTypes($types);
        if ($iteratorType === null) {
            return null;
        }

        $unionTypes = [];
        $lastException = null;
        foreach ($types as $singleDocBlockType) {
            try {
                $singleDocBlockType = $this->getTypeInArray($singleDocBlockType);
                if ($singleDocBlockType !== null) {
                    $subGraphQlType = $topToGraphQLType($singleDocBlockType, null);
                    //$subGraphQlType = $this->toGraphQlType($singleDocBlockType, null, false, $refMethod, $docBlockObj);

                    // By convention, we trim the NonNull part of the "$subGraphQlType"
                    if ($subGraphQlType instanceof NonNull) {
                        $subGraphQlType = $subGraphQlType->getWrappedType();
                        assert($subGraphQlType instanceof OutputType && $subGraphQlType instanceof GraphQLType);
                    }
                } else {
                    $subGraphQlType = null;
                }

                $unionTypes[] = $topToGraphQLType($iteratorType, $subGraphQlType);
            } catch (CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;

                if ($singleDocBlockType !== null && $isOutputType) {
                    // The type is an array (like User[]). Let's use that.
                    $valueType = $topToGraphQLType($singleDocBlockType, null);
                    if ($valueType !== null) {
                        $unionTypes[] = new ListOfType($valueType);
                    }
                }
            }
        }

        if (empty($unionTypes) && $lastException !== null) {
            // We have an issue, let's try without the subType
            try {
                $result = $topToGraphQLType($iteratorType, null);
            } catch (CannotMapTypeExceptionInterface $otherException) {
                // Still an issue? Let's rethrow the previous exception.
                throw $lastException;
            }

            return $result;

            //return $this->mapDocBlockType($type, $docBlockType, $isNullable, false, $refMethod, $docBlockObj);
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
            /*} elseif ($isOutputType) {
                // This clearly cannot work. We are only gathering types from arrays and we cannot join arrays (I think)
                $graphQlType = new UnionType($unionTypes, $this->recursiveTypeMapper);
                $graphQlType = $this->typeRegistry->getOrRegisterType($graphQlType);
                Assert::isInstanceOf($graphQlType, OutputType::class);*/
        } else {
            // There are no union input types. Something went wrong.
            $graphQlType = null;
        }

        return $graphQlType;
    }

    /**
     * Removes the iterator type from $types
     *
     * @param Type[] $types
     */
    private function splitIteratorFromOtherTypes(array &$types): ?Type
    {
        $iteratorType = null;
        $key = null;
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

        return $iteratorType;
    }
}
