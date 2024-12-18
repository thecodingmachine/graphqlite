<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver as PhpDocumentorTypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\InvalidDocBlockRuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Parameters\DefaultValueParameter;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\InputTypeProperty;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockFactory;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

use function array_map;
use function array_unique;
use function assert;
use function count;
use function explode;
use function in_array;
use function iterator_to_array;
use function reset;
use function trim;

use const PHP_EOL;
use const SORT_REGULAR;

class TypeHandler implements ParameterHandlerInterface
{
    private PhpDocumentorTypeResolver $phpDocumentorTypeResolver;

    public function __construct(
        private readonly ArgumentResolver $argumentResolver,
        private readonly RootTypeMapperInterface $rootTypeMapper,
        private readonly TypeResolver $typeResolver,
        private readonly DocBlockFactory $docBlockFactory,
    )
    {
        $this->phpDocumentorTypeResolver = new PhpDocumentorTypeResolver();
    }

    public function mapReturnType(
        ReflectionMethod $refMethod,
        DocBlock $docBlockObj,
    ): GraphQLType&OutputType
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType !== null) {
            $phpdocType = $this->reflectionTypeToPhpDocType($returnType, $refMethod->getDeclaringClass());
        } else {
            $phpdocType = new Mixed_();
        }

        $docBlockReturnType = $this->getDocBlocReturnType($docBlockObj, $refMethod);

        try {
            $type = $this->mapType(
                $phpdocType,
                $docBlockReturnType,
                $returnType && $returnType->allowsNull(),
                false,
                $refMethod,
                $docBlockObj,
            );
            assert(! $type instanceof InputType);
        } catch (CannotMapTypeExceptionInterface $e) {
            $e->addReturnInfo($refMethod);
            throw $e;
        }

        return $type;
    }

    private function getDocBlocReturnType(DocBlock $docBlock, ReflectionMethod $refMethod): Type|null
    {
        /** @var array<int,Return_> $returnTypeTags */
        $returnTypeTags = $docBlock->getTagsByName('return');
        if (count($returnTypeTags) > 1) {
            throw InvalidDocBlockRuntimeException::tooManyReturnTags($refMethod);
        }
        $docBlockReturnType = null;
        if (isset($returnTypeTags[0])) {
            $docBlockReturnType = $returnTypeTags[0]->getType();
        }

        return $docBlockReturnType;
    }

    /**
     * Gets property type from its dock block.
     */
    private function getDocBlockPropertyType(DocBlock $docBlock, ReflectionProperty $refProperty): Type|null
    {
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        if (! $varTags) {
            // If we don't have any @var tags, was this property promoted, and if so, do we have an
            // @param tag on the constructor docblock?  If so, use that for the type.
            if ($refProperty->isPromoted()) {
                $refConstructor = $refProperty->getDeclaringClass()->getConstructor();
                if (! $refConstructor) {
                    return null;
                }

                $docBlock = $this->docBlockFactory->create($refConstructor);
                $paramTags = $docBlock->getTagsByName('param');
                foreach ($paramTags as $paramTag) {
                    if (! $paramTag instanceof Param) {
                        continue;
                    }

                    if ($paramTag->getVariableName() === $refProperty->getName()) {
                        return $paramTag->getType();
                    }
                }
            }

            return null;
        }

        if (count($varTags) > 1) {
            throw InvalidDocBlockRuntimeException::tooManyVarTags($refProperty);
        }

        return reset($varTags)->getType();
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, Type|null $paramTagType, ParameterAnnotations $parameterAnnotations): ParameterInterface
    {
        $hideParameter = $parameterAnnotations->getAnnotationByType(HideParameter::class);
        if ($hideParameter) {
            if ($parameter->isDefaultValueAvailable() === false) {
                throw CannotHideParameterRuntimeException::needDefaultValue($parameter);
            }

            return new DefaultValueParameter($parameter->getDefaultValue());
        }

        $useInputType = $parameterAnnotations->getAnnotationByType(UseInputType::class);
        if ($useInputType !== null) {
            try {
                $type = $this->typeResolver->mapNameToInputType($useInputType->getInputType());
            } catch (CannotMapTypeExceptionInterface $e) {
                $e->addParamInfo($parameter);
                throw $e;
            }
        } else {
            $parameterType = $parameter->getType();
            $allowsNull = $parameterType === null || $parameterType->allowsNull();

            if ($parameterType === null) {
                $phpdocType = new Mixed_();
                $allowsNull = false;
                //throw MissingTypeHintException::missingTypeHint($parameter);
            } else {
                $declaringClass = $parameter->getDeclaringClass();
                assert($declaringClass !== null);
                $phpdocType = $this->reflectionTypeToPhpDocType($parameterType, $declaringClass);
            }

            try {
                $declaringFunction = $parameter->getDeclaringFunction();
                if (! $declaringFunction instanceof ReflectionMethod) {
                    throw new InvalidArgumentException('Parameter of a function passed. Only parameters of methods are supported.');
                }
                $type = $this->mapType(
                    $phpdocType,
                    $paramTagType,
                    $allowsNull || $parameter->isDefaultValueAvailable(),
                    true,
                    $declaringFunction,
                    $docBlock,
                    $parameter->getName(),
                );
                assert($type instanceof InputType);
            } catch (CannotMapTypeExceptionInterface $e) {
                $e->addParamInfo($parameter);
                throw $e;
            }
        }

        $description = $this->getParameterDescriptionFromDocBlock($docBlock, $parameter);

        $hasDefaultValue = false;
        $defaultValue = null;
        if ($parameter->allowsNull()) {
            $hasDefaultValue = true;
        }
        if ($parameter->isDefaultValueAvailable()) {
            $hasDefaultValue = true;
            $defaultValue = $parameter->getDefaultValue();
        }

        return new InputTypeParameter(
            name: $parameter->getName(),
            type: $type,
            description: $description,
            hasDefaultValue: $hasDefaultValue,
            defaultValue: $defaultValue,
            argumentResolver: $this->argumentResolver,
        );
    }

    private function getParameterDescriptionFromDocBlock(DocBlock $docBlock, ReflectionParameter $parameter): string|null
    {
        /** @var DocBlock\Tags\Param[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');

        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() === $parameter->getName()) {
                return $paramTag->getDescription()?->render();
            }
        }

        return null;
    }

    /**
     * Map class property to a GraphQL type.
     *
     * @return (InputType&GraphQLType)|(OutputType&GraphQLType)
     *
     * @throws CannotMapTypeException
     */
    public function mapPropertyType(
        ReflectionProperty $refProperty,
        DocBlock $docBlock,
        bool $toInput,
        string|null $argumentName = null,
        bool|null $isNullable = null,
    ): GraphQLType
    {
        $propertyType = $refProperty->getType();
        if ($propertyType !== null) {
            $phpdocType = $this->reflectionTypeToPhpDocType($propertyType, $refProperty->getDeclaringClass());
        } else {
            $phpdocType = new Mixed_();
        }

        $docBlockPropertyType = $this->getDocBlockPropertyType($docBlock, $refProperty);

        if ($isNullable === null) {
            $isNullable = $propertyType && $propertyType->allowsNull();
        }

        return $this->mapType(
            $phpdocType,
            $docBlockPropertyType,
            $isNullable,
            $toInput,
            $refProperty,
            $docBlock,
            $argumentName,
        );
    }

    /**
     * Maps class property into input property.
     *
     * @throws CannotMapTypeException
     */
    public function mapInputProperty(
        ReflectionProperty $refProperty,
        DocBlock $docBlock,
        string|null $argumentName = null,
        string|null $inputTypeName = null,
        mixed $defaultValue = null,
        bool|null $isNullable = null,
    ): InputTypeProperty
    {
        $docBlockComment = $docBlock->getSummary() . PHP_EOL . $docBlock->getDescription()->render();

        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');
        $varTag = reset($varTags);
        if ($varTag) {
            $docBlockComment .= PHP_EOL . $varTag->getDescription();

            if ($isNullable === null) {
                $varType = $varTag->getType();
                if ($varType !== null) {
                    $isNullable = in_array('null', explode('|', (string) $varType), true);
                }
            }
        }

        if ($isNullable === null) {
            $isNullable = $refProperty->getType()?->allowsNull() ?? false;
        }

        if ($inputTypeName) {
            $inputType = $this->typeResolver->mapNameToInputType($inputTypeName);
        } else {
            $inputType = $this->mapPropertyType($refProperty, $docBlock, true, $argumentName, $isNullable);
            assert(! $inputType instanceof OutputType);
        }

        $hasDefault = $defaultValue !== null || $isNullable;
        $fieldName = $argumentName ?? $refProperty->getName();

        return new InputTypeProperty(
            propertyName: $refProperty->getName(),
            fieldName: $fieldName,
            type: $inputType,
            description: trim($docBlockComment),
            hasDefaultValue: $hasDefault,
            defaultValue: $defaultValue,
            argumentResolver: $this->argumentResolver,
        );
    }

    /**
     * @return (InputType&GraphQLType)|(OutputType&GraphQLType)
     *
     * @throws CannotMapTypeException
     */
    private function mapType(
        Type $type,
        Type|null $docBlockType,
        bool $isNullable,
        bool $mapToInputType,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj,
        string|null $argumentName = null,
    ): GraphQLType
    {
        $graphQlType = null;
        if ($isNullable && ! $type instanceof Nullable) {
            // In case a parameter has a default value, let's wrap the main type in a nullable
            $type = new Nullable($type);
        }
        $innerType = $type instanceof Nullable ? $type->getActualType() : $type;

        if (
            $innerType instanceof Array_
            || $innerType instanceof Iterable_
            || $innerType instanceof Mixed_
            // Try to match generic phpdoc-provided iterables with non-generic return-type-provided iterables
            // Example: (return type `\ArrayObject`, phpdoc `\ArrayObject<string, TestObject>`)
            || ($innerType instanceof Object_
                && $docBlockType instanceof Collection
                && (string) $innerType->getFqsen() === (string) $docBlockType->getFqsen()
            )
        ) {
            // We need to use the docBlockType
            if ($docBlockType === null) {
                throw CannotMapTypeException::createForMissingPhpDoc($innerType, $reflector, $argumentName);
            }
            if ($mapToInputType === true) {
                assert($argumentName !== null);
                $graphQlType = $this->rootTypeMapper->toGraphQLInputType($docBlockType, null, $argumentName, $reflector, $docBlockObj);
            } else {
                $graphQlType = $this->rootTypeMapper->toGraphQLOutputType($docBlockType, null, $reflector, $docBlockObj);
            }
        } else {
            $completeType = $this->appendTypes($type, $docBlockType);
            if ($mapToInputType === true) {
                assert($argumentName !== null);
                $graphQlType = $this->rootTypeMapper->toGraphQLInputType($completeType, null, $argumentName, $reflector, $docBlockObj);
            } else {
                $graphQlType = $this->rootTypeMapper->toGraphQLOutputType($completeType, null, $reflector, $docBlockObj);
            }
        }

        return $graphQlType;
    }

    /**
     * Appends types together, eventually creating a Compound type and removing duplicates if any.
     */
    private function appendTypes(Type $type, Type|null $docBlockType): Type
    {
        if ($docBlockType === null) {
            return $type;
        }

        if ($type === $docBlockType) {
            return $type;
        }

        $types = [$type];
        if ($docBlockType instanceof Compound) {
            $docBlockTypes = iterator_to_array($docBlockType);
            $types = [...$types, ...$docBlockTypes];
        } else {
            $types[] = $docBlockType;
        }

        // Normalize types by changing ?string into string|null
        $newTypes = [];
        foreach ($types as $currentType) {
            if ($currentType instanceof Nullable) {
                $newTypes[] = $currentType->getActualType();
                $newTypes[] = new Null_();
            } else {
                $newTypes[] = $currentType;
            }
        }

        $types = array_unique($newTypes, SORT_REGULAR);

        if (count($types) === 1) {
            return $types[0];
        }

        return new Compound($types);
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function reflectionTypeToPhpDocType(ReflectionType $type, ReflectionClass $reflectionClass): Type
    {
        assert($type instanceof ReflectionNamedType || $type instanceof ReflectionUnionType);
        if ($type instanceof ReflectionNamedType) {
            $phpdocType = $this->phpDocumentorTypeResolver->resolve($type->getName());
            $phpdocType = $this->resolveSelf($phpdocType, $reflectionClass);

            if ($type->allowsNull()) {
                $phpdocType = new Nullable($phpdocType);
            }

            return $phpdocType;
        }
        return new Compound(
            array_map(
                function ($namedType) use ($reflectionClass): Type {
                    assert($namedType instanceof ReflectionNamedType);
                    $phpdocType = $this->phpDocumentorTypeResolver->resolve($namedType->getName());
                    $phpdocType = $this->resolveSelf($phpdocType, $reflectionClass);

                    if ($namedType->allowsNull()) {
                        $phpdocType = new Nullable($phpdocType);
                    }
                    return $phpdocType;
                },
                $type->getTypes(),
            ),
        );
    }

    /**
     * Resolves "self" types into the class type.
     *
     * @param ReflectionClass<object> $reflectionClass
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\' . $reflectionClass->getName()));
        }

        return $type;
    }
}
