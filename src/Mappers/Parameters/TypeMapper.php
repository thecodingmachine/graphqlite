<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters;


use function array_filter;
use function count;
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
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\InvalidDocBlockException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\TypeMappingException;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Types\UnionType;

class TypeMapper implements ParameterMapperInterface
{
    /**
     * @var PhpDocumentorTypeResolver
     */
    private $phpDocumentorTypeResolver;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;
    /**
     * @var RootTypeMapperInterface
     */
    private $rootTypeMapper;
    /**
     * @var TypeResolver
     */
    private $typeResolver;
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(RecursiveTypeMapperInterface $typeMapper,
                                ArgumentResolver $argumentResolver,
                                RootTypeMapperInterface $rootTypeMapper,
                                TypeResolver $typeResolver,
                                AnnotationReader $annotationReader)
    {
        $this->recursiveTypeMapper = $typeMapper;
        $this->argumentResolver = $argumentResolver;
        $this->rootTypeMapper = $rootTypeMapper;
        $this->phpDocumentorTypeResolver = new PhpDocumentorTypeResolver();
        $this->typeResolver = $typeResolver;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @return GraphQLType&OutputType
     */
    public function mapReturnType(ReflectionMethod $refMethod, DocBlock $docBlockObj): GraphQLType
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType !== null) {
            $phpdocType = $this->phpDocumentorTypeResolver->resolve((string) $returnType);
            $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
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

    private function getDocBlocReturnType(DocBlock $docBlock, \ReflectionMethod $refMethod): ?Type
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

    /**
     * @param array<string, DocBlock\Tags\Param> $paramTags
     */
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, array $paramTags): ParameterInterface
    {
        $useInputType = $this->annotationReader->getUseInputTypeAnnotation($parameter);
        if ($useInputType) {
            try {
                $type = $this->typeResolver->mapNameToInputType($useInputType->getType());
            } catch (CannotMapTypeExceptionInterface $e) {
                throw CannotMapTypeException::wrapWithParamInfo($e, $parameter);
            }
        } else {
            $parameterType = $parameter->getType();
            $allowsNull = $parameterType === null ? true : $parameterType->allowsNull();

            $type = (string) $parameterType;
            if ($type === '') {
                $phpdocType = new Mixed_();
                $allowsNull = false;
                //throw MissingTypeHintException::missingTypeHint($parameter);
            } else {
                $phpdocType = $this->phpDocumentorTypeResolver->resolve($type);
                $phpdocType = $this->resolveSelf($phpdocType, $parameter->getDeclaringClass());
            }

            $docBlockType = $paramTags[$parameter->getName()] ?? null;

            try {
                $type = $this->mapType($phpdocType, $docBlockType, $allowsNull || $parameter->isDefaultValueAvailable(), true, $parameter->getDeclaringFunction(), $docBlock, $parameter->getName());
            } catch (TypeMappingException $e) {
                throw TypeMappingException::wrapWithParamInfo($e, $parameter);
            } catch (CannotMapTypeExceptionInterface $e) {
                throw CannotMapTypeException::wrapWithParamInfo($e, $parameter);
            }
        }

        $hasDefaultValue = false;
        $defaultValue = null;
        if ($parameter->allowsNull()) {
            $hasDefaultValue = true;
        }
        if ($parameter->isDefaultValueAvailable()) {
            $hasDefaultValue = true;
            $defaultValue = $parameter->getDefaultValue();
        }

        return new InputTypeParameter($parameter->getName(), $type, $hasDefaultValue, $defaultValue, $this->argumentResolver);
    }

    private function mapType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, string $argumentName = null): GraphQLType
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Iterable_ || $type instanceof Mixed_) {
            $graphQlType = $this->mapDocBlockType($type, $docBlockType, $isNullable, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
        } else {
            try {
                $graphQlType = $this->toGraphQlType($type, null, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
                if (!$isNullable) {
                    $graphQlType = GraphQLType::nonNull($graphQlType);
                }
            } catch (TypeMappingException | CannotMapTypeExceptionInterface $e) {
                // Is the type iterable? If yes, let's analyze the docblock
                // TODO: it would be better not to go through an exception for this.
                if ($type instanceof Object_) {
                    $fqcn = (string) $type->getFqsen();
                    $refClass = new ReflectionClass($fqcn);
                    // Note : $refClass->isIterable() is only accessible in PHP 7.2
                    if ($refClass->implementsInterface(Iterator::class) || $refClass->implementsInterface(IteratorAggregate::class)) {
                        $graphQlType = $this->mapIteratorDocBlockType($type, $docBlockType, $isNullable, $refMethod, $docBlockObj, $argumentName);
                    } else {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }
        }

        return $graphQlType;
    }

    private function mapDocBlockType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, string $argumentName = null): GraphQLType
    {
        if ($docBlockType === null) {
            throw TypeMappingException::createFromType($type);
        }
        if (!$isNullable) {
            // Let's check a "null" value in the docblock
            $isNullable = $this->isNullable($docBlockType);
        }

        $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockType);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingException::createFromType($type);
        }

        $unionTypes = [];
        $lastException = null;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            try {
                $unionTypes[] = $this->toGraphQlType($this->dropNullableType($singleDocBlockType), null, $mapToInputType, $refMethod, $docBlockObj, $argumentName);
            } catch (TypeMappingException | CannotMapTypeExceptionInterface $e) {
                // We have several types. It is ok not to be able to match one.
                $lastException = $e;
            }
        }

        if (empty($unionTypes) && $lastException !== null) {
            throw $lastException;
        }

        if (count($unionTypes) === 1) {
            $graphQlType = $unionTypes[0];
        } else {
            $graphQlType = new UnionType($unionTypes, $this->recursiveTypeMapper);
        }

        /* elseif (count($filteredDocBlockTypes) === 1) {
            $graphQlType = $this->toGraphQlType($filteredDocBlockTypes[0], $mapToInputType);
        } else {
            throw new GraphQLException('Union types are not supported (yet)');
            //$graphQlTypes = array_map([$this, 'toGraphQlType'], $filteredDocBlockTypes);
            //$$graphQlType = new UnionType($graphQlTypes);
        }*/

        if (!$isNullable) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }
        return $graphQlType;
    }

    /**
     * Maps a type where the main PHP type is an iterator
     */
    private function mapIteratorDocBlockType(Type $type, ?Type $docBlockType, bool $isNullable, ReflectionMethod $refMethod, DocBlock $docBlockObj, string $argumentName = null): GraphQLType
    {
        if ($docBlockType === null) {
            throw TypeMappingException::createFromType($type);
        }
        if (!$isNullable) {
            // Let's check a "null" value in the docblock
            $isNullable = $this->isNullable($docBlockType);
        }

        $filteredDocBlockTypes = $this->typesWithoutNullable($docBlockType);
        if (empty($filteredDocBlockTypes)) {
            throw TypeMappingException::createFromType($type);
        }

        $unionTypes = [];
        $lastException = null;
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            try {
                $singleDocBlockType = $this->getTypeInArray($singleDocBlockType);
                if ($singleDocBlockType !== null) {
                    $subGraphQlType = $this->toGraphQlType($singleDocBlockType, null, false, $refMethod, $docBlockObj);
                } else {
                    $subGraphQlType = null;
                }

                $unionTypes[] = $this->toGraphQlType($type, $subGraphQlType, false, $refMethod, $docBlockObj);

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

        if (!$isNullable) {
            $graphQlType = GraphQLType::nonNull($graphQlType);
        }
        return $graphQlType;
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @param Type $type
     * @param GraphQLType|null $subType
     * @param bool $mapToInputType
     * @return GraphQLType (InputType&GraphQLType)|(OutputType&GraphQLType)
     * @throws CannotMapTypeExceptionInterface
     */
    private function toGraphQlType(Type $type, ?GraphQLType $subType, bool $mapToInputType, ReflectionMethod $refMethod, DocBlock $docBlockObj, string $argumentName = null): GraphQLType
    {
        if ($mapToInputType === true) {
            $mappedType = $this->rootTypeMapper->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        } else {
            $mappedType = $this->rootTypeMapper->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }
        if ($mappedType === null) {
            throw TypeMappingException::createFromType($type);
        }
        return $mappedType;
    }

    /**
     * Removes "null" from the type (if it is compound). Return an array of types (not a Compound type).
     *
     * @param Type $docBlockTypeHint
     * @return array
     */
    private function typesWithoutNullable(Type $docBlockTypeHint): array
    {
        if ($docBlockTypeHint instanceof Compound) {
            $docBlockTypeHints = \iterator_to_array($docBlockTypeHint);
        } else {
            $docBlockTypeHints = [$docBlockTypeHint];
        }
        return array_filter($docBlockTypeHints, function ($item) {
            return !$item instanceof Null_;
        });
    }

    /**
     * Drops "Nullable" types and return the core type.
     *
     * @param Type $typeHint
     * @return Type
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
     *
     * @param Type $typeHint
     * @return Type|null
     */
    private function getTypeInArray(Type $typeHint): ?Type
    {
        $typeHint = $this->dropNullableType($typeHint);

        if (!$typeHint instanceof Array_) {
            return null;
        }

        return $this->dropNullableType($typeHint->getValueType());
    }

    /**
     * @param Type $docBlockTypeHint
     * @return bool
     */
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
     *
     * @param Type $type
     * @return Type
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\'.$reflectionClass->getName()));
        }
        return $type;
    }
}
