<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
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
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use Webmozart\Assert\Assert;

use function array_merge;
use function array_unique;
use function assert;
use function count;
use function explode;
use function in_array;
use function iterator_to_array;
use function method_exists;
use function reset;
use function trim;

use const PHP_EOL;
use const SORT_REGULAR;

class TypeHandler implements ParameterHandlerInterface
{
    /** @var PhpDocumentorTypeResolver */
    private $phpDocumentorTypeResolver;

    /** @var ArgumentResolver */
    private $argumentResolver;

    /** @var RootTypeMapperInterface */
    private $rootTypeMapper;

    /** @var TypeResolver */
    private $typeResolver;

    public function __construct(
        ArgumentResolver $argumentResolver,
        RootTypeMapperInterface $rootTypeMapper,
        TypeResolver $typeResolver
    ) {
        $this->argumentResolver = $argumentResolver;
        $this->rootTypeMapper = $rootTypeMapper;
        $this->phpDocumentorTypeResolver = new PhpDocumentorTypeResolver();
        $this->typeResolver = $typeResolver;
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
            $type = $this->mapType(
                $phpdocType,
                $docBlockReturnType,
                $returnType ? $returnType->allowsNull() : false,
                false,
                $refMethod,
                $docBlockObj
            );
            assert($type instanceof GraphQLType && $type instanceof OutputType);
        } catch (CannotMapTypeExceptionInterface $e) {
            $e->addReturnInfo($refMethod);
            throw $e;
        }

        return $type;
    }

    private function getDocBlocReturnType(DocBlock $docBlock, ReflectionMethod $refMethod): ?Type
    {
        /** @var Return_[] $returnTypeTags */
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
    private function getDocBlockPropertyType(DocBlock $docBlock, ReflectionProperty $refProperty): ?Type
    {
        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');

        if (!$varTags) {
            return null;
        }

        if (count($varTags) > 1) {
            throw InvalidDocBlockRuntimeException::tooManyVarTags($refProperty);
        }

        return reset($varTags)->getType();
    }

    public function mapParameter(
        ReflectionParameter $parameter,
        DocBlock $docBlock,
        ?Type $paramTagType,
        ParameterAnnotations $parameterAnnotations
    ): ParameterInterface {
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
            $allowsNull = $parameterType === null ? true : $parameterType->allowsNull();

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
                Assert::isInstanceOf(
                    $declaringFunction,
                    ReflectionMethod::class,
                    'Parameter of a function passed. Only parameters of methods are supported.'
                );
                $type = $this->mapType(
                    $phpdocType,
                    $paramTagType,
                    $allowsNull || $parameter->isDefaultValueAvailable(),
                    true,
                    $declaringFunction,
                    $docBlock,
                    $parameter->getName()
                );
                Assert::isInstanceOf($type, InputType::class);
            } catch (CannotMapTypeExceptionInterface $e) {
                $e->addParamInfo($parameter);
                throw $e;
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

        return new InputTypeParameter(
            $parameter->getName(),
            $type,
            $hasDefaultValue,
            $defaultValue,
            $this->argumentResolver
        );
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
        ?string $argumentName = null,
        ?bool $isNullable = null
    ): GraphQLType {
        $propertyType = null;

        // getType function on property reflection is available only since PHP 7.4
        if (method_exists($refProperty, 'getType')) {
            $propertyType = $refProperty->getType();
            if ($propertyType !== null) {
                $phpdocType = $this->reflectionTypeToPhpDocType($propertyType, $refProperty->getDeclaringClass());
            } else {
                $phpdocType = new Mixed_();
            }
        } else {
            $phpdocType = new Mixed_();
        }

        $docBlockPropertyType = $this->getDocBlockPropertyType($docBlock, $refProperty);

        if ($isNullable === null) {
            $isNullable = $propertyType ? $propertyType->allowsNull() : false;
        }

        return $this->mapType(
            $phpdocType,
            $docBlockPropertyType,
            $isNullable,
            $toInput,
            $refProperty,
            $docBlock,
            $argumentName
        );
    }

    /**
     * Maps class property into input property.
     *
     * @param mixed $defaultValue
     *
     * @throws CannotMapTypeException
     */
    public function mapInputProperty(
        ReflectionProperty $refProperty,
        DocBlock $docBlock,
        ?string $argumentName = null,
        ?string $inputTypeName = null,
        $defaultValue = null,
        ?bool $isNullable = null
    ): InputTypeProperty {
        $docBlockComment = $docBlock->getSummary() . PHP_EOL . $docBlock->getDescription()->render();

        /** @var Var_[] $varTags */
        $varTags = $docBlock->getTagsByName('var');
        $varTag = reset($varTags);
        if ($varTag) {
            $docBlockComment .= PHP_EOL . $varTag->getDescription();

            if ($isNullable === null) {
                $varType = $varTag->getType();
                if ($varType !== null) {
                    $isNullable = in_array('null', explode('|', (string)$varType));
                }
            }
        }

        if ($isNullable === null) {
            $isNullable = false;
            // getType function on property reflection is available only since PHP 7.4
            if (method_exists($refProperty, 'getType')) {
                $refType = $refProperty->getType();
                if ($refType !== null) {
                    $isNullable = $refType->allowsNull();
                }
            }
        }

        if ($inputTypeName) {
            $inputType = $this->typeResolver->mapNameToInputType($inputTypeName);
        } else {
            $inputType = $this->mapPropertyType($refProperty, $docBlock, true, $argumentName, $isNullable);
            assert($inputType instanceof InputType && $inputType instanceof GraphQLType);
        }

        $hasDefault = $defaultValue !== null || $isNullable;
        $fieldName = $argumentName ?? $refProperty->getName();

        $inputProperty = new InputTypeProperty(
            $refProperty->getName(),
            $fieldName,
            $inputType,
            $hasDefault,
            $defaultValue,
            $this->argumentResolver
        );
        $inputProperty->setDescription(trim($docBlockComment));

        return $inputProperty;
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflector
     *
     * @return (InputType&GraphQLType)|(OutputType&GraphQLType)
     *
     * @throws CannotMapTypeException
     */
    private function mapType(
        Type $type,
        ?Type $docBlockType,
        bool $isNullable,
        bool $mapToInputType,
        $reflector,
        DocBlock $docBlockObj,
        ?string $argumentName = null
    ): GraphQLType {
        $graphQlType = null;
        if ($isNullable && !$type instanceof Nullable) {
            // In case a parameter has a default value, let's wrap the main type in a nullable
            $type = new Nullable($type);
        }
        $innerType = $type instanceof Nullable ? $type->getActualType() : $type;

        if ($innerType instanceof Array_ || $innerType instanceof Iterable_ || $innerType instanceof Mixed_) {
            // We need to use the docBlockType
            if ($docBlockType === null) {
                throw CannotMapTypeException::createForMissingPhpDoc($innerType, $reflector, $argumentName);
            }
            if ($mapToInputType === true) {
                Assert::notNull($argumentName);
                $graphQlType = $this->rootTypeMapper->toGraphQLInputType(
                    $docBlockType,
                    null,
                    $argumentName,
                    $reflector,
                    $docBlockObj
                );
            } else {
                $graphQlType = $this->rootTypeMapper->toGraphQLOutputType(
                    $docBlockType,
                    null,
                    $reflector,
                    $docBlockObj
                );
            }
        } else {
            $completeType = $this->appendTypes($type, $docBlockType);
            if ($mapToInputType === true) {
                Assert::notNull($argumentName);
                $graphQlType = $this->rootTypeMapper->toGraphQLInputType(
                    $completeType,
                    null,
                    $argumentName,
                    $reflector,
                    $docBlockObj
                );
            } else {
                $graphQlType = $this->rootTypeMapper->toGraphQLOutputType(
                    $completeType,
                    null,
                    $reflector,
                    $docBlockObj
                );
            }
        }

        return $graphQlType;
    }

    /**
     * Appends types together, eventually creating a Compound type and removing duplicates if any.
     */
    private function appendTypes(Type $type, ?Type $docBlockType): Type
    {
        if ($docBlockType === null) {
            return $type;
        }

        if ($type === $docBlockType) {
            return $type;
        }

        $types = [$type];

        // Try to match generic phpdoc-provided iterables with non-generic return-type-provided iterables
        // Example: (return type `\ArrayObject`, phpdoc `\ArrayObject<string, TestObject>`)
        if ($docBlockType instanceof Collection
            && $type instanceof Object_
            && (string)$type->getFqsen() === (string)$docBlockType->getFqsen()
        ) {
            $types = [];
        }

        if ($docBlockType instanceof Compound) {
            $docBlockTypes = iterator_to_array($docBlockType);
            $types = array_merge($types, $docBlockTypes);
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

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    private function reflectionTypeToPhpDocType(ReflectionType $type, ReflectionClass $reflectionClass): Type
    {
        assert($type instanceof ReflectionNamedType);
        $phpdocType = $this->phpDocumentorTypeResolver->resolve($type->getName());
        Assert::notNull($phpdocType);

        $phpdocType = $this->resolveSelf($phpdocType, $reflectionClass);

        if ($type->allowsNull()) {
            $phpdocType = new Nullable($phpdocType);
        }

        return $phpdocType;
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
