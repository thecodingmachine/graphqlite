<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver as PhpDocumentorTypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionType;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\InvalidDocBlockException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\CompositeFail;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\FailForType;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\Fail;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\Result;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\Success;
use TheCodingMachine\GraphQLite\Mappers\Parameters\Result\UnexpectedResultException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\TypeMappingException;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Types\UnionType;
use Webmozart\Assert\Assert;
use function array_filter;
use function count;
use function iterator_to_array;

class TypeMapper implements ParameterMapperInterface
{
    /** @var PhpDocumentorTypeResolver */
    private $phpDocumentorTypeResolver;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var ArgumentResolver */
    private $argumentResolver;
    /** @var RootTypeMapperInterface */
    private $rootTypeMapper;
    /** @var TypeResolver */
    private $typeResolver;

    public function __construct(
        RecursiveTypeMapperInterface $typeMapper,
        ArgumentResolver $argumentResolver,
        RootTypeMapperInterface $rootTypeMapper,
        TypeResolver $typeResolver
    ) {
        $this->recursiveTypeMapper       = $typeMapper;
        $this->argumentResolver          = $argumentResolver;
        $this->rootTypeMapper            = $rootTypeMapper;
        $this->phpDocumentorTypeResolver = new PhpDocumentorTypeResolver();
        $this->typeResolver              = $typeResolver;
    }

    /**
     * @return GraphQLType&OutputType
     */
    public function mapReturnType(ReflectionMethod $refMethod, DocBlock $docBlockObj): GraphQLType
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType !== null) {
            $phpdocType = $this->reflectionTypeToPhpDocType($returnType, $refMethod->getDeclaringClass());
        } else {
            $phpdocType = new Mixed_();
        }

        $docBlockReturnType = $this->getDocBlocReturnType($docBlockObj, $refMethod);

        try {
            /** @var GraphQLType&OutputType $type */
            $type = $this->mapType($phpdocType, $docBlockReturnType, $returnType ? $returnType->allowsNull() : false, false, $refMethod, $docBlockObj);
        } catch (TypeMappingException $e) {
            throw TypeMappingException::wrapWithReturnInfo($e, $refMethod);
        } catch (CannotMapTypeExceptionInterface $e) {
            throw CannotMapTypeException::wrapWithReturnInfo($e, $refMethod);
        }

        return $type;
    }

    private function getDocBlocReturnType(DocBlock $docBlock, ReflectionMethod $refMethod): ?Type
    {
        /** @var Return_[] $returnTypeTags */
        $returnTypeTags = $docBlock->getTagsByName('return');
        if (count($returnTypeTags) > 1) {
            throw InvalidDocBlockException::tooManyReturnTags($refMethod);
        }
        $docBlockReturnType = null;
        if (isset($returnTypeTags[0])) {
            $docBlockReturnType = $returnTypeTags[0]->getType();
        }

        return $docBlockReturnType;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ?Parameter $parameterAnnotation): ParameterInterface
    {
        if ($parameterAnnotation && $parameterAnnotation->getInputType() !== null) {
            try {
                $type = $this->typeResolver->mapNameToInputType($parameterAnnotation->getInputType());
            } catch (CannotMapTypeExceptionInterface $e) {
                throw CannotMapTypeException::wrapWithParamInfo($e, $parameter);
            }
        } else {
            $parameterType = $parameter->getType();
            $allowsNull    = $parameterType === null ? true : $parameterType->allowsNull();

            if ($parameterType === null) {
                $phpdocType = new Mixed_();
                $allowsNull = false;
                //throw MissingTypeHintException::missingTypeHint($parameter);
            } else {
                $declaringClass = $parameter->getDeclaringClass();
                Assert::notNull($declaringClass);
                $phpdocType = $this->reflectionTypeToPhpDocType($parameterType, $declaringClass);
            }

            try {
                $declaringFunction = $parameter->getDeclaringFunction();
                Assert::isInstanceOf($declaringFunction, ReflectionMethod::class, 'Parameter of a function passed. Only parameters of methods are supported.');
                $type = $this->mapType($phpdocType, $paramTagType, $allowsNull || $parameter->isDefaultValueAvailable(), true, $declaringFunction, $docBlock, $parameter->getName());
            } catch (TypeMappingException $e) {
                throw TypeMappingException::wrapWithParamInfo($e, $parameter);
            } catch (CannotMapTypeExceptionInterface $e) {
                throw CannotMapTypeException::wrapWithParamInfo($e, $parameter);
            }
        }

        $hasDefaultValue = false;
        $defaultValue    = null;
        if ($parameter->allowsNull()) {
            $hasDefaultValue = true;
        }
        if ($parameter->isDefaultValueAvailable()) {
            $hasDefaultValue = true;
            $defaultValue    = $parameter->getDefaultValue();
        }

        return new InputTypeParameter($parameter->getName(), $type, $hasDefaultValue, $defaultValue, $this->argumentResolver);
    }

    private function mapType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, ?string $argumentName = null): GraphQLType
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Iterable_ || $type instanceof Mixed_) {
            $graphQlType = $this->mapDocBlockType($type, $docBlockType, $isNullable, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
        } else {
            try {
                $result = $this->toGraphQlType($type, null, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
                switch (true) {
                    case $result instanceof Success:
                        $graphQlType = $result->getType();
                        // The type is non nullable if the PHP argument is non nullable
                        // There is an exception: if the PHP argument is non nullable but points to a factory that can called without passing any argument,
                        // then, the input type is nullable (and we can still create an empty object).
                        if (! $isNullable && (! $graphQlType instanceof ResolvableMutableInputObjectType || $graphQlType->isInstantiableWithoutParameters() === false)) {
                            $graphQlType = GraphQLType::nonNull($graphQlType);
                        }
                        break;
                    case $result instanceof Fail:
                        // Is the type iterable? If yes, let's analyze the docblock
                        if (! ($type instanceof Object_)) {
                            return $result;
                        }

                        $fqcn = (string) $type->getFqsen();
                        $refClass = new ReflectionClass($fqcn);
                        // Note : $refClass->isIterable() is only accessible in PHP 7.2
                        if (! $refClass->implementsInterface(Iterator::class) && ! $refClass->implementsInterface(IteratorAggregate::class)) {
                            return $result;
                        }

                        $graphQlType = $this->mapIteratorDocBlockType($type, $docBlockType, $isNullable, $refMethod, $docBlockObj, $argumentName);

                        break;
                    default:
                        throw UnexpectedResultException::create();
                }
            } catch (CannotMapTypeExceptionInterface $e) {
                // Is the type iterable? If yes, let's analyze the docblock
                // TODO: it would be better not to go through an exception for this.
                if (! ($type instanceof Object_)) {
                    throw $e;
                }

                $fqcn     = (string) $type->getFqsen();
                $refClass = new ReflectionClass($fqcn);
                // Note : $refClass->isIterable() is only accessible in PHP 7.2
                if (! $refClass->implementsInterface(Iterator::class) && ! $refClass->implementsInterface(IteratorAggregate::class)) {
                    throw $e;
                }

                $graphQlType = $this->mapIteratorDocBlockType($type, $docBlockType, $isNullable, $refMethod, $docBlockObj, $argumentName);
            }
        }

        return $graphQlType;
    }

    private function mapDocBlockType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, ?string $argumentName = null): GraphQLType
    {
        if ($docBlockType === null) {
            throw TypeMappingException::createFromType($type);
        }
        if (! $isNullable) {
            // Let's check a "null" value in the docblock
            $isNullable = $this->isNullable($docBlockType);
        }

        $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockType);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingException::createFromType($type);
        }

        $unionTypes    = [];
        $lastException = null;
        $fails = new CompositeFail();
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            try {
                $result = $this->toGraphQlType($this->dropNullableType($singleDocBlockType), null, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
                switch (true) {
                    case $result instanceof Success:
                        $unionTypes[] = $result->getType();
                        break;
                    case $result instanceof Fail:
                        $fails->addFail($result);
                        break;
                    default:
                        throw UnexpectedResultException::create();
                }
            } catch (CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;
            }
        }

        if (empty($unionTypes)) {
            $fails->throwIfError();
        }

        // TODO migrate this to CompositeFail
        if (empty($unionTypes) && $lastException !== null) {
            throw $lastException;
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            $badTypes = [];
            foreach ($unionTypes as $unionType) {
                if ($unionType instanceof ObjectType) {
                    continue;
                }

                $badTypes[] = $unionType;
            }
            if ($badTypes !== []) {
                throw CannotMapTypeException::createForBadTypeInUnion($unionTypes);
            }

            $graphQlType = new UnionType($unionTypes, $this->recursiveTypeMapper);
        }

        if (! $isNullable) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }

        return $graphQlType;
    }

    /**
     * Maps a type where the main PHP type is an iterator
     */
    private function mapIteratorDocBlockType(Type $type, ?Type $docBlockType, bool $isNullable, ReflectionMethod $refMethod, DocBlock $docBlockObj, ?string $argumentName = null): GraphQLType
    {
        if ($docBlockType === null) {
            throw TypeMappingException::createFromType($type);
        }
        if (! $isNullable) {
            // Let's check a "null" value in the docblock
            $isNullable = $this->isNullable($docBlockType);
        }

        $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockType);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingException::createFromType($type);
        }

        $unionTypes    = [];
        $lastException = null;
        $fails = new CompositeFail();
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            try {
                $singleDocBlockType = $this->getTypeInArray($singleDocBlockType);
                if ($singleDocBlockType !== null) {
                    $result = $this->toGraphQlType($singleDocBlockType, null, false, $refMethod, $docBlockObj);
                    switch (true) {
                        case $result instanceof Success:
                            $subGraphQlType = $result->getType();
                            break;
                        case $result instanceof Fail:
                            $fails->addFail($result);
                            continue 2;
                        default:
                            throw UnexpectedResultException::create();
                    }
                } else {
                    $subGraphQlType = null;
                }

                $result = $this->toGraphQlType($type, $subGraphQlType, false, $refMethod, $docBlockObj);
                switch (true) {
                    case $result instanceof Success:
                        $unionTypes[] = $result->getType();
                        break;
                    case $result instanceof Fail:
                        $fails->addFail($result);
                        break;
                    default:
                        throw UnexpectedResultException::create();
                }

                // TODO: add here a scan of the $type variable and do stuff if it is iterable.
                // TODO: remove the iterator type if specified in the docblock (@return Iterator|User[])
                // TODO: check there is at least one array (User[])
            } catch (TypeMappingException | CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;
            }
        }

        if (empty($unionTypes) && $lastException !== null) {
            // We have an issue, let's try without the subType
            return $this->mapDocBlockType($type, $docBlockType, $isNullable, false, $refMethod, $docBlockObj);
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            $graphQlType = new UnionType($unionTypes, $this->recursiveTypeMapper);
        }

        if (! $isNullable) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }

        return $graphQlType;
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @return GraphQLType (InputType&GraphQLType)|(OutputType&GraphQLType)
     *
     * @throws CannotMapTypeExceptionInterface
     */
    private function toGraphQlType(Type $type, ?GraphQLType $subType, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, ?string $argumentName = null): Result
    {
        if ($mapToInputType === true) {
            Assert::nullOrIsInstanceOf($subType, InputType::class);
            Assert::notNull($argumentName);
            $mappedType = $this->rootTypeMapper->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        } else {
            Assert::nullOrIsInstanceOf($subType, OutputType::class);
            $mappedType = $this->rootTypeMapper->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }
        if ($mappedType === null) {
            return FailForType::createFromType($type);
        }

        return new Success($mappedType);
    }

    /**
     * Removes "null" from the type (if it is compound). Return an array of types (not a Compound type).
     *
     * @return Type[]
     */
    private function typesWithoutNullable(Type $docBlockTypeHint): array
    {
        if ($docBlockTypeHint instanceof Compound) {
            $docBlockTypeHints = iterator_to_array($docBlockTypeHint);
        } else {
            $docBlockTypeHints = [$docBlockTypeHint];
        }

        return array_filter($docBlockTypeHints, static function ($item) {
            return ! $item instanceof Null_;
        });
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

    /**
     * Resolves a list type.
     */
    private function getTypeInArray(Type $typeHint): ?Type
    {
        $typeHint = $this->dropNullableType($typeHint);

        if (! $typeHint instanceof Array_) {
            return null;
        }

        return $this->dropNullableType($typeHint->getValueType());
    }

    private function isNullable(Type $docBlockTypeHint): bool
    {
        if ($docBlockTypeHint instanceof Null_) {
            return true;
        }
        if ($docBlockTypeHint instanceof Compound) {
            foreach ($docBlockTypeHint as $type) {
                if ($type instanceof Null_) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolves "self" types into the class type.
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\' . $reflectionClass->getName()));
        }

        return $type;
    }

    private function reflectionTypeToPhpDocType(ReflectionType $type, ReflectionClass $reflectionClass): Type
    {
        $phpdocType = $this->phpDocumentorTypeResolver->resolve((string) $type);
        Assert::notNull($phpdocType);

        return $this->resolveSelf($phpdocType, $reflectionClass);
    }
}
