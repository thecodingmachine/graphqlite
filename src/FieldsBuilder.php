<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationException;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Annotations\AbstractRequest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\InvalidParameterException;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\DuplicateMappingException;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewareInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\TypeHandler;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldHandlerInterface;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Middlewares\MissingMagicGetException;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

use function array_diff_key;
use function array_fill_keys;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function array_shift;
use function array_slice;
use function assert;
use function count;
use function get_parent_class;
use function in_array;
use function is_callable;
use function is_string;
use function key;
use function reset;
use function rtrim;
use function str_starts_with;
use function trim;

use const PHP_EOL;

/**
 * A class in charge if returning list of fields for queries / mutations / entities / input types
 */
class FieldsBuilder
{
    private TypeHandler $typeMapper;

    public function __construct(
        private readonly AnnotationReader              $annotationReader,
        private readonly RecursiveTypeMapperInterface  $recursiveTypeMapper,
        private readonly ArgumentResolver              $argumentResolver,
        private readonly TypeResolver                  $typeResolver,
        private readonly CachedDocBlockFactory         $cachedDocBlockFactory,
        private readonly NamingStrategyInterface       $namingStrategy,
        private readonly RootTypeMapperInterface       $rootTypeMapper,
        private readonly ParameterMiddlewareInterface  $parameterMapper,
        private readonly FieldMiddlewareInterface      $fieldMiddleware,
        private readonly InputFieldMiddlewareInterface $inputFieldMiddleware,
    )
    {
        $this->typeMapper = new TypeHandler($this->argumentResolver, $this->rootTypeMapper, $this->typeResolver);
    }

    /**
     * @return array<string, FieldDefinition>
     *
     * @throws ReflectionException
     */
    public function getQueries(object $controller): array
    {
        return $this->getFieldsByAnnotations($controller, Query::class, false);
    }

    /**
     * @return array<string, FieldDefinition>
     *
     * @throws ReflectionException
     */
    public function getMutations(object $controller): array
    {
        return $this->getFieldsByAnnotations($controller, Mutation::class, false);
    }

    /** @return array<string, FieldDefinition> QueryField indexed by name. */
    public function getFields(object $controller, string|null $typeName = null): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($controller, Annotations\Field::class, true, $typeName);

        $refClass = new ReflectionClass($controller);

        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        if ($controller instanceof FromSourceFieldsInterface) {
            $sourceFields = [...$sourceFields, ...$controller->getSourceFields()];
        }

        $fieldsFromSourceFields = $this->getQueryFieldsFromSourceFields($sourceFields, $refClass);

        $fields = $fieldAnnotations;
        foreach ($fieldsFromSourceFields as $field) {
            $fields[$field->name] = $field;
        }

        return $fields;
    }

    /**
     * @param class-string<object> $className
     *
     * @return array<string,InputField>
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function getInputFields(string $className, string $inputName, bool $isUpdate = false): array
    {
        $refClass = new ReflectionClass($className);

        /** @var ReflectionMethod[]|ReflectionProperty[] $reflectorByFields */
        $reflectorByFields = [];

        $inputFields = [];
        $defaultProperties = $refClass->getDefaultProperties();

        $closestMatchingTypeClass = null;
        $parent = get_parent_class($refClass->getName());
        if ($parent !== false) {
            $closestMatchingTypeClass = $this->recursiveTypeMapper->findClosestMatchingParent($parent);
        }

        /** @var ReflectionProperty[]|ReflectionMethod[] $reflectors */
        $reflectors = [...$refClass->getProperties(), ...$refClass->getMethods(ReflectionMethod::IS_PUBLIC)];
        foreach ($reflectors as $reflector) {
            if ($closestMatchingTypeClass !== null && $closestMatchingTypeClass === $reflector->getDeclaringClass()->getName()) {
                // Optimisation: no need to fetch annotations from parent classes that are ALREADY GraphQL types.
                // We will merge the fields anyway.
                continue;
            }

            if ($reflector instanceof ReflectionMethod) {
                $fields = $this->getInputFieldsByMethodAnnotations($className, $refClass, $reflector, Field::class, false, $defaultProperties, $inputName, $isUpdate);
            } else {
                $fields = $this->getInputFieldsByPropertyAnnotations($className, $refClass, $reflector, Field::class, $defaultProperties, $inputName, $isUpdate);
            }

            $duplicates = array_intersect_key($reflectorByFields, $fields);
            if ($duplicates) {
                $name = key($duplicates);
                assert(is_string($name));
                throw DuplicateMappingException::createForQuery($refClass->getName(), $name, $reflectorByFields[$name], $reflector);
            }

            $reflectorByFields = array_merge(
                $reflectorByFields,
                array_fill_keys(array_keys($fields), $reflector),
            );

            $inputFields = array_merge($inputFields, $fields);
        }

        // Make sure @Field annotations applied to parent's private properties are taken into account as well.
        $parent = $refClass->getParentClass();
        if ($parent) {
            $parentFields = $this->getInputFields($parent->getName(), $inputName, $isUpdate);
            $inputFields = array_merge($inputFields, array_diff_key($parentFields, $inputFields));
        }

        return $inputFields;
    }

    /**
     * Track Field annotation in a self targeted type
     *
     * @param class-string<object> $className
     *
     * @return array<string, FieldDefinition> QueryField indexed by name.
     */
    public function getSelfFields(string $className, string|null $typeName = null): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($className, Annotations\Field::class, false, $typeName);

        $refClass = new ReflectionClass($className);

        /** @var SourceFieldInterface[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        $fieldsFromSourceFields = $this->getQueryFieldsFromSourceFields($sourceFields, $refClass);

        $fields = $fieldAnnotations;
        foreach ($fieldsFromSourceFields as $field) {
            $fields[$field->name] = $field;
        }

        return $fields;
    }

    /**
     * @param ReflectionMethod $refMethod A method annotated with a Factory annotation.
     * @param int $skip Skip first N parameters if those are passed in externally
     *
     * @return array<string, ParameterInterface> Returns an array of parameters.
     */
    public function getParameters(ReflectionMethod $refMethod, int $skip = 0): array
    {
        $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
        //$docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();

        $parameters = array_slice($refMethod->getParameters(), $skip);

        return $this->mapParameters($parameters, $docBlockObj);
    }

    /**
     * @param ReflectionMethod $refMethod A method annotated with a Decorate annotation.
     *
     * @return array<string, ParameterInterface> Returns an array of parameters.
     */
    public function getParametersForDecorator(ReflectionMethod $refMethod): array
    {
        // First parameter of a decorator is always $source so we're skipping that.
        return $this->getParameters($refMethod, 1);
    }

    /**
     * @param object|class-string<object> $controller The controller instance, or the name of the source class name
     * @param class-string<AbstractRequest> $annotationName
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field (unless @Type has no class attribute), false for @Query and @Mutation
     * @param string|null $typeName Type name for which fields should be extracted for.
     *
     * @return array<string, FieldDefinition>
     *
     * @throws ReflectionException
     */
    private function getFieldsByAnnotations($controller, string $annotationName, bool $injectSource, string|null $typeName = null): array
    {
        $refClass = new ReflectionClass($controller);
        /** @var array<string, FieldDefinition> $queryList */
        $queryList = [];

        /** @var ReflectionMethod[]|ReflectionProperty[] $reflectorByFields */
        $reflectorByFields = [];

        $oldDeclaringClass = null;
        $context = null;

        $closestMatchingTypeClass = null;
        if ($annotationName === Field::class) {
            $parent = get_parent_class($refClass->getName());
            if ($parent !== false) {
                $closestMatchingTypeClass = $this->recursiveTypeMapper->findClosestMatchingParent($parent);
            }
        }

        /** @var ReflectionProperty[]|ReflectionMethod[] $reflectors */
        $reflectors = [...$refClass->getProperties(), ...$refClass->getMethods(ReflectionMethod::IS_PUBLIC)];
        foreach ($reflectors as $reflector) {
            if ($closestMatchingTypeClass !== null && $closestMatchingTypeClass === $reflector->getDeclaringClass()->getName()) {
                // Optimisation: no need to fetch annotations from parent classes that are ALREADY GraphQL types.
                // We will merge the fields anyway.
                continue;
            }

            if ($reflector instanceof ReflectionMethod) {
                $fields = $this->getFieldsByMethodAnnotations($controller, $refClass, $reflector, $annotationName, $injectSource, $typeName);
            } else {
                $fields = $this->getFieldsByPropertyAnnotations($controller, $refClass, $reflector, $annotationName, $typeName);
            }

            $duplicates = array_intersect_key($reflectorByFields, $fields);
            if ($duplicates) {
                $name = key($duplicates);
                assert(is_string($name));
                throw DuplicateMappingException::createForQuery($refClass->getName(), $name, $reflectorByFields[$name], $reflector);
            }

            $reflectorByFields = array_merge(
                $reflectorByFields,
                array_fill_keys(array_keys($fields), $reflector),
            );

            $queryList = array_merge($queryList, $fields);
        }

        return $queryList;
    }

    /**
     * Gets fields by class method annotations.
     *
     * @param class-string<AbstractRequest> $annotationName
     *
     * @return array<string, FieldDefinition>
     *
     * @throws AnnotationException
     */
    private function getFieldsByMethodAnnotations(string|object $controller, ReflectionClass $refClass, ReflectionMethod $refMethod, string $annotationName, bool $injectSource, string|null $typeName = null): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getMethodAnnotations($refMethod, $annotationName);
        foreach ($annotations as $queryAnnotation) {
            $description = null;
            $methodName = $refMethod->getName();

            if ($queryAnnotation instanceof Field) {
                if (str_starts_with($methodName, 'set')) {
                    continue;
                }
                $for = $queryAnnotation->getFor();
                if ($typeName && $for && !in_array($typeName, $for)) {
                    continue;
                }

                $description = $queryAnnotation->getDescription();
            }

            $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);

            $name = $queryAnnotation->getName() ?: $this->namingStrategy->getFieldNameFromMethodName($methodName);

            if (!$description) {
                $description = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();
            }

            $outputType = $queryAnnotation->getOutputType();
            if ($outputType) {
                try {
                    $type = $this->typeResolver->mapNameToOutputType($outputType);
                } catch (CannotMapTypeExceptionInterface $e) {
                    $e->addReturnInfo($refMethod);
                    throw $e;
                }
            } else {
                $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
            }

            $fieldDescriptor = new QueryFieldDescriptor(
                name: $name,
                type: $type,
                comment: trim($description),
                deprecationReason: $this->getDeprecationReason($docBlockObj),
                refMethod: $refMethod,
            );

            $parameters = $refMethod->getParameters();
            if ($injectSource === true) {
                $firstParameter = array_shift($parameters);
                // TODO: check that $first_parameter type is correct.
            }

            // TODO: remove once support for deprecated prefetchMethod on Field is removed.
            $prefetchDataParameter = $this->getPrefetchParameter($name, $refClass, $refMethod, $queryAnnotation);

            if ($prefetchDataParameter) {
                array_shift($parameters);
            }

            $args = $this->mapParameters($parameters, $docBlockObj);

            // TODO: remove once support for deprecated prefetchMethod on Field is removed.
            if ($prefetchDataParameter) {
                $args = ['__graphqlite_prefectData' => $prefetchDataParameter, ...$args];
            }

            $fieldDescriptor = $fieldDescriptor->withParameters($args);

            if (is_string($controller)) {
                $fieldDescriptor = $fieldDescriptor->withTargetMethodOnSource($refMethod->getDeclaringClass()->getName(), $methodName);
            } else {
                $callable = [$controller, $methodName];
                assert(is_callable($callable));
                $fieldDescriptor = $fieldDescriptor->withCallable($callable);
            }

            $fieldDescriptor = $fieldDescriptor
                ->withInjectSource($injectSource)
                ->withMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refMethod));

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
                {
                    return QueryField::fromFieldDescriptor($fieldDescriptor);
                }
            });

            if ($field === null) {
                continue;
            }

            if (isset($fields[$name])) {
                throw DuplicateMappingException::createForQueryInOneMethod($name, $refMethod);
            }
            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * Gets fields by class property annotations.
     *
     * @param class-string<AbstractRequest> $annotationName
     *
     * @return array<string, FieldDefinition>
     *
     * @throws AnnotationException
     */
    private function getFieldsByPropertyAnnotations(string|object $controller, ReflectionClass $refClass, ReflectionProperty $refProperty, string $annotationName, string|null $typeName = null): array
    {
        $fields = [];
        $annotations = $this->annotationReader->getPropertyAnnotations($refProperty, $annotationName);
        foreach ($annotations as $queryAnnotation) {
            $description = null;

            if ($queryAnnotation instanceof Field) {
                $for = $queryAnnotation->getFor();
                if ($typeName && $for && !in_array($typeName, $for)) {
                    continue;
                }

                $description = $queryAnnotation->getDescription();
            }

            $docBlock = $this->cachedDocBlockFactory->getDocBlock($refProperty);

            $name = $queryAnnotation->getName() ?: $refProperty->getName();

            if (!$description) {
                $description = $docBlock->getSummary() . PHP_EOL . $docBlock->getDescription()->render();

                /** @var Var_[] $varTags */
                $varTags = $docBlock->getTagsByName('var');
                $varTag = reset($varTags);
                if ($varTag) {
                    $description .= PHP_EOL . $varTag->getDescription();
                }
            }

            $outputType = $queryAnnotation->getOutputType();
            if ($outputType) {
                $type = $this->typeResolver->mapNameToOutputType($outputType);
            } else {
                $type = $this->typeMapper->mapPropertyType($refProperty, $docBlock, false);
                assert($type instanceof OutputType);
            }

            $fieldDescriptor = new QueryFieldDescriptor(
                name: $name,
                type: $type,
                comment: trim($description),
                deprecationReason: $this->getDeprecationReason($docBlock),
                refProperty: $refProperty,
            );

            if (is_string($controller)) {
                $fieldDescriptor = $fieldDescriptor->withTargetPropertyOnSource($refProperty->getDeclaringClass()->getName(), $refProperty->getName());
            } else {
                $fieldDescriptor = $fieldDescriptor->withCallable(static function () use ($controller, $refProperty) {
                    return PropertyAccessor::getValue($controller, $refProperty->getName());
                });
            }

            $fieldDescriptor = $fieldDescriptor
                ->withInjectSource(false)
                ->withMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refProperty));

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
                {
                    return QueryField::fromFieldDescriptor($fieldDescriptor);
                }
            });

            if ($field === null) {
                continue;
            }

            if (isset($fields[$name])) {
                throw DuplicateMappingException::createForQueryInOneProperty($name, $refProperty);
            }
            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * @param SourceFieldInterface[] $sourceFields
     * @param ReflectionClass<object> $refClass
     *
     * @return FieldDefinition[]
     *
     * @throws CannotMapTypeException
     * @throws CannotMapTypeExceptionInterface
     * @throws ReflectionException
     */
    private function getQueryFieldsFromSourceFields(array $sourceFields, ReflectionClass $refClass): array
    {
        if (empty($sourceFields)) {
            return [];
        }

        $typeField = $this->annotationReader->getTypeAnnotation($refClass);
        $extendTypeField = $this->annotationReader->getExtendTypeAnnotation($refClass);

        if ($typeField !== null) {
            $objectClass = $typeField->getClass();
        } elseif ($extendTypeField !== null) {
            $objectClass = $extendTypeField->getClass();
            if ($objectClass === null) {
                // We need to be able to fetch the mapped PHP class from the object type!
                $typeName = $extendTypeField->getName();
                assert($typeName !== null);
                $targetedType = $this->recursiveTypeMapper->mapNameToType($typeName);
                if (!$targetedType instanceof MutableObjectType) {
                    throw CannotMapTypeException::extendTypeWithBadTargetedClass($refClass->getName(), $extendTypeField);
                }
                $objectClass = $targetedType->getMappedClassName();
                if ($objectClass === null) {
                    throw new CannotMapTypeException('@ExtendType(name="' . $extendTypeField->getName() . '") points to a GraphQL type that does not map a PHP class. Therefore, you cannot use the @SourceField annotation in conjunction with this @ExtendType.');
                }
            }
        } else {
            throw MissingAnnotationException::missingTypeExceptionToUseSourceField();
        }

        $objectRefClass = new ReflectionClass($objectClass);

        $oldDeclaringClass = null;
        $context = null;
        $queryList = [];

        foreach ($sourceFields as $sourceField) {
            if (!$sourceField->shouldFetchFromMagicProperty()) {
                try {
                    $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getSourceName() ?? $sourceField->getName());
                } catch (FieldNotFoundException $e) {
                    throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
                }

                $methodName = $refMethod->getName();

                $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
                $docBlockComment = rtrim($docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render());

                $deprecated = $docBlockObj->getTagsByName('deprecated');
                if (count($deprecated) >= 1) {
                    $deprecationReason = trim((string)$deprecated[0]);
                }

                $description = $sourceField->getDescription() ?? $docBlockComment;
                $args = $this->mapParameters($refMethod->getParameters(), $docBlockObj, $sourceField);

                $outputType = $sourceField->getOutputType();
                $phpTypeStr = $sourceField->getPhpType();
                if ($outputType !== null) {
                    $type = $this->resolveOutputType($outputType, $refClass, $sourceField);
                } elseif ($phpTypeStr !== null) {
                    $type = $this->resolvePhpType($phpTypeStr, $refClass, $refMethod);
                } else {
                    $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
                }

                $fieldDescriptor = new QueryFieldDescriptor(
                    name: $sourceField->getName(),
                    type: $type,
                    parameters: $args,
                    targetClass: $refMethod->getDeclaringClass()->getName(),
                    targetMethodOnSource: $methodName,
                    comment: $description,
                    deprecationReason: $deprecationReason ?? null,
                    refMethod: $refMethod,
                );
            } else {
                $outputType = $sourceField->getOutputType();
                if ($outputType !== null) {
                    $type = $this->resolveOutputType($outputType, $refClass, $sourceField);
                } else {
                    $phpTypeStr = $sourceField->getPhpType();
                    assert($phpTypeStr !== null);
                    $magicGefRefMethod = $this->getMagicGetMethodFromSourceClassOrProxy($refClass);

                    $type = $this->resolvePhpType($phpTypeStr, $refClass, $magicGefRefMethod);
                }

                $fieldDescriptor = new QueryFieldDescriptor(
                    name: $sourceField->getName(),
                    type: $type,
                    targetClass: $refClass->getName(),
                    magicProperty: $sourceField->getSourceName() ?? $sourceField->getName(),
                    comment: $sourceField->getDescription(),
                );
            }

            $fieldDescriptor = $fieldDescriptor
                ->withInjectSource(false)
                ->withMiddlewareAnnotations($sourceField->getMiddlewareAnnotations());

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
                {
                    return QueryField::fromFieldDescriptor($fieldDescriptor);
                }
            });

            if ($field === null) {
                continue;
            }

            $queryList[] = $field;
        }

        return $queryList;
    }

    /**
     * @throws ReflectionException
     * @throws MissingAnnotationException
     * @throws MissingMagicGetException
     */
    private function getMagicGetMethodFromSourceClassOrProxy(ReflectionClass $proxyRefClass): ReflectionMethod
    {
        $magicGet = '__get';
        if ($proxyRefClass->hasMethod($magicGet)) {
            return $proxyRefClass->getMethod($magicGet);
        }

        $typeField = $this->annotationReader->getTypeAnnotation($proxyRefClass);
        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException($proxyRefClass->getName());
        }

        $sourceClassName = $typeField->getClass();
        $sourceRefClass = new ReflectionClass($sourceClassName);
        if (!$sourceRefClass->hasMethod($magicGet)) {
            throw MissingMagicGetException::cannotFindMagicGet($sourceClassName);
        }

        return $sourceRefClass->getMethod($magicGet);
    }

    /** @param ReflectionClass<object> $refClass */
    private function resolveOutputType(string $outputType, ReflectionClass $refClass, SourceFieldInterface $sourceField): OutputType&Type
    {
        try {
            return $this->typeResolver->mapNameToOutputType($outputType);
        } catch (CannotMapTypeExceptionInterface $e) {
            $e->addSourceFieldInfo($refClass, $sourceField);
            throw $e;
        }
    }

    /** @param ReflectionClass<object> $refClass */
    private function resolvePhpType(string $phpTypeStr, ReflectionClass $refClass, ReflectionMethod $refMethod): OutputType&Type
    {
        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        $context = $this->cachedDocBlockFactory->getContextFromClass($refClass);
        $phpdocType = $typeResolver->resolve($phpTypeStr, $context);
        assert($phpdocType !== null);

        $fakeDocBlock = new DocBlock('', null, [new DocBlock\Tags\Return_($phpdocType)], $context);
        return $this->typeMapper->mapReturnType($refMethod, $fakeDocBlock);

        // TODO: add a catch to CannotMapTypeExceptionInterface and a "addMagicFieldInfo" method to know where the issues are coming from.
    }

    /**
     * @param ReflectionClass<T> $reflectionClass
     *
     * @template T of object
     */
    private function getMethodFromPropertyName(ReflectionClass $reflectionClass, string $propertyName): ReflectionMethod
    {
        if ($reflectionClass->hasMethod($propertyName)) {
            $methodName = $propertyName;
        } else {
            $methodName = PropertyAccessor::findGetter($reflectionClass->getName(), $propertyName);
            if (!$methodName) {
                throw FieldNotFoundException::missingField($reflectionClass->getName(), $propertyName);
            }
        }

        return $reflectionClass->getMethod($methodName);
    }

    /**
     * @param ReflectionParameter[] $refParameters
     *
     * @return array<string, ParameterInterface>
     */
    private function mapParameters(array $refParameters, DocBlock $docBlock, SourceFieldInterface|null $sourceField = null): array
    {
        if (empty($refParameters)) {
            return [];
        }
        $additionalParameterAnnotations = $sourceField?->getParameterAnnotations() ?? [];

        $docBlockTypes = [];
        /** @var DocBlock\Tags\Param[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');
        foreach ($paramTags as $paramTag) {
            $docBlockTypes[$paramTag->getVariableName()] = $paramTag->getType();
        }

        $parameterAnnotationsPerParameter = $this->annotationReader->getParameterAnnotationsPerParameter($refParameters);

        foreach ($refParameters as $parameter) {
            $parameterAnnotations = $parameterAnnotationsPerParameter[$parameter->getName()] ?? new ParameterAnnotations([]);
            //$parameterAnnotations = $this->annotationReader->getParameterAnnotations($parameter);
            if (!empty($additionalParameterAnnotations[$parameter->getName()])) {
                $parameterAnnotations->merge($additionalParameterAnnotations[$parameter->getName()]);
                unset($additionalParameterAnnotations[$parameter->getName()]);
            }

            $parameterObj = $this->parameterMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotations, $this->typeMapper);

            $args[$parameter->getName()] = $parameterObj;
        }

        // Sanity check, are the parameters declared in $additionalParameterAnnotations available in $refParameters?
        if (!empty($additionalParameterAnnotations)) {
            $refParameter = reset($refParameters);
            foreach ($additionalParameterAnnotations as $parameterName => $parameterAnnotations) {
                foreach ($parameterAnnotations->getAllAnnotations() as $annotation) {
                    $refMethod = $refParameter->getDeclaringFunction();
                    assert($refMethod instanceof ReflectionMethod);
                    throw InvalidParameterException::parameterNotFoundFromSourceField($refParameter->getName(), $annotation::class, $refMethod);
                }
            }
        }

        return $args;
    }

    /**
     * Extracts deprecation reason from doc block.
     */
    private function getDeprecationReason(DocBlock $docBlockObj): string|null
    {
        $deprecated = $docBlockObj->getTagsByName('deprecated');
        if (count($deprecated) >= 1) {
            return trim((string)$deprecated[0]);
        }

        return null;
    }

    /**
     * Extracts prefetch method info from annotation.
     *
     * TODO: remove once support for deprecated prefetchMethod on Field is removed.
     *
     * @throws InvalidArgumentException
     */
    private function getPrefetchParameter(
        string $fieldName,
        ReflectionClass $refClass,
        ReflectionMethod|ReflectionProperty $reflector,
        object $annotation,
    ): PrefetchDataParameter|null
    {
        if ($annotation instanceof Field) {
            $prefetchMethodName = $annotation->getPrefetchMethod();
            if ($prefetchMethodName !== null) {
                try {
                    $prefetchRefMethod = $refClass->getMethod($prefetchMethodName);
                } catch (ReflectionException $e) {
                    throw InvalidPrefetchMethodRuntimeException::methodNotFound($reflector, $refClass, $prefetchMethodName, $e);
                }

                $prefetchParameters = $prefetchRefMethod->getParameters();
                array_shift($prefetchParameters);

                $prefetchDocBlockObj = $this->cachedDocBlockFactory->getDocBlock($prefetchRefMethod);
                $prefetchArgs = $this->mapParameters($prefetchParameters, $prefetchDocBlockObj);

                return new PrefetchDataParameter(
                    fieldName: $fieldName,
                    resolver: static function (array $sources, ...$args) use ($prefetchMethodName) {
                        $source = $sources[0];

                        return $source->{$prefetchMethodName}($sources, ...$args);
                    },
                    parameters: $prefetchArgs,
                );
            }
        }

        return null;
    }

    /**
     * Gets input fields by class method annotations.
     *
     * @param class-string<AbstractRequest> $annotationName
     * @param array<mixed> $defaultProperties
     *
     * @return array<string, InputField>
     *
     * @throws AnnotationException
     */
    private function getInputFieldsByMethodAnnotations(string|object $controller, ReflectionClass $refClass, ReflectionMethod $refMethod, string $annotationName, bool $injectSource, array $defaultProperties, string|null $typeName = null, bool $isUpdate = false): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getMethodAnnotations($refMethod, $annotationName);
        foreach ($annotations as $fieldAnnotations) {
            $description = null;
            if (!($fieldAnnotations instanceof Field)) {
                continue;
            }

            $for = $fieldAnnotations->getFor();
            if ($typeName && $for && !in_array($typeName, $for)) {
                continue;
            }
            $description = $fieldAnnotations->getDescription();

            $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $methodName = $refMethod->getName();
            if (!str_starts_with($methodName, 'set')) {
                continue;
            }
            $name = $fieldAnnotations->getName() ?: $this->namingStrategy->getInputFieldNameFromMethodName($methodName);
            if (!$description) {
                $description = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();
            }

            $parameters = $refMethod->getParameters();
            if ($injectSource === true) {
                $firstParameter = array_shift($parameters);
                // TODO: check that $first_parameter type is correct.
            }

            /** @var array<string, InputTypeParameterInterface> $args */
            $args = $this->mapParameters($parameters, $docBlockObj);

            $inputType = $fieldAnnotations->getInputType();
            if ($inputType) {
                try {
                    $type = $this->typeResolver->mapNameToInputType($inputType);
                } catch (CannotMapTypeExceptionInterface $e) {
                    $e->addReturnInfo($refMethod);
                    throw $e;
                }
            } else {
                $type = $args[$name]->getType();
                if ($isUpdate && $type instanceof NonNull) {
                    $type = $type->getWrappedType();
                }
            }

            assert($type instanceof InputType);

            $inputFieldDescriptor = new InputFieldDescriptor(
                name: $name,
                type: $type,
                parameters: $args,
                comment: trim($description),
                refMethod: $refMethod,
                isUpdate: $isUpdate,
            );

            $inputFieldDescriptor = $inputFieldDescriptor
                ->withHasDefaultValue($isUpdate)
                ->withDefaultValue($args[$name]->getDefaultValue());
            $constructerParameters = $this->getClassConstructParameterNames($refClass);
            if (!in_array($name, $constructerParameters)) {
                $inputFieldDescriptor = $inputFieldDescriptor->withTargetMethodOnSource($refMethod->getDeclaringClass()->getName(), $methodName);
            }

            $inputFieldDescriptor = $inputFieldDescriptor
                ->withInjectSource($injectSource)
                ->withMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refMethod));

            $field = $this->inputFieldMiddleware->process($inputFieldDescriptor, new class implements InputFieldHandlerInterface {
                public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
                {
                    return InputField::fromFieldDescriptor($inputFieldDescriptor);
                }
            });

            if ($field === null) {
                continue;
            }

            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * Gets input fields by class property annotations.
     *
     * @param class-string<AbstractRequest> $annotationName
     * @param array<mixed> $defaultProperties
     *
     * @return array<string, InputField>
     *
     * @throws AnnotationException
     */
    private function getInputFieldsByPropertyAnnotations(string|object $controller, ReflectionClass $refClass, ReflectionProperty $refProperty, string $annotationName, array $defaultProperties, string|null $typeName = null, bool $isUpdate = false): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getPropertyAnnotations($refProperty, $annotationName);
        $docBlock = $this->cachedDocBlockFactory->getDocBlock($refProperty);
        foreach ($annotations as $annotation) {
            $description = null;

            if (!($annotation instanceof Field)) {
                continue;
            }

            $for = $annotation->getFor();
            if ($typeName && $for && !in_array($typeName, $for)) {
                continue;
            }

            $description = $annotation->getDescription();
            $name = $annotation->getName() ?: $refProperty->getName();
            $inputType = $annotation->getInputType();
            $constructerParameters = $this->getClassConstructParameterNames($refClass);
            $inputProperty = $this->typeMapper->mapInputProperty($refProperty, $docBlock, $name, $inputType, $defaultProperties[$refProperty->getName()] ?? null, $isUpdate ? true : null);

            if (!$description) {
                $description = $inputProperty->getDescription();
            }

            if (in_array($name, $constructerParameters)) {
                $middlewareAnnotations = $this->annotationReader->getPropertyAnnotations($refProperty, MiddlewareAnnotationInterface::class);
                if ($middlewareAnnotations !== []) {
                    throw IncompatibleAnnotationsException::middlewareAnnotationsUnsupported();
                }
                // constructor hydrated
                $field = new InputField(
                    $name,
                    $inputProperty->getType(),
                    [$inputProperty->getName() => $inputProperty],
                    null,
                    null,
                    trim($description),
                    $isUpdate,
                    $inputProperty->hasDefaultValue(),
                    $inputProperty->getDefaultValue(),
                );
            } else {
                $type = $inputProperty->getType();
                if (!$inputType && $isUpdate && $type instanceof NonNull) {
                    $type = $type->getWrappedType();
                }
                assert($type instanceof InputType);

                // setters and properties
                $inputFieldDescriptor = new InputFieldDescriptor(
                    name: $inputProperty->getName(),
                    type: $type,
                    parameters: [$inputProperty->getName() => $inputProperty],
                    targetClass: $refProperty->getDeclaringClass()->getName(),
                    targetPropertyOnSource: $refProperty->getName(),
                    injectSource: false,
                    comment: trim($description),
                    middlewareAnnotations: $this->annotationReader->getMiddlewareAnnotations($refProperty),
                    refProperty: $refProperty,
                    isUpdate: $isUpdate,
                    hasDefaultValue: $inputProperty->hasDefaultValue(),
                    defaultValue: $inputProperty->getDefaultValue(),
                );

                $field = $this->inputFieldMiddleware->process($inputFieldDescriptor, new class implements InputFieldHandlerInterface {
                    public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
                    {
                        return InputField::fromFieldDescriptor($inputFieldDescriptor);
                    }
                });
            }

            if ($field === null) {
                continue;
            }

            $fields[$name] = $field;
        }

        return $fields;
    }

    /** @return string[] */
    private function getClassConstructParameterNames(ReflectionClass $refClass): array
    {
        $constructor = $refClass->getConstructor();

        if (!$constructor) {
            return [];
        }

        $names = [];
        foreach ($constructor->getParameters() as $parameter) {
            $names[] = $parameter->getName();
        }

        return $names;
    }
}
