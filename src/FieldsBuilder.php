<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationException;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
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
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;
use Webmozart\Assert\Assert;

use function array_diff_key;
use function array_fill_keys;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function array_shift;
use function assert;
use function count;
use function get_class;
use function get_parent_class;
use function in_array;
use function is_string;
use function key;
use function reset;
use function rtrim;
use function trim;

use const PHP_EOL;

/**
 * A class in charge if returning list of fields for queries / mutations / entities / input types
 */
class FieldsBuilder
{
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var CachedDocBlockFactory */
    private $cachedDocBlockFactory;
    /** @var TypeResolver */
    private $typeResolver;
    /** @var NamingStrategyInterface */
    private $namingStrategy;
    /** @var TypeHandler */
    private $typeMapper;
    /** @var ParameterMiddlewareInterface */
    private $parameterMapper;
    /** @var FieldMiddlewareInterface */
    private $fieldMiddleware;
    /** @var InputFieldMiddlewareInterface */
    private $inputFieldMiddleware;

    public function __construct(
        AnnotationReader $annotationReader,
        RecursiveTypeMapperInterface $typeMapper,
        ArgumentResolver $argumentResolver,
        TypeResolver $typeResolver,
        CachedDocBlockFactory $cachedDocBlockFactory,
        NamingStrategyInterface $namingStrategy,
        RootTypeMapperInterface $rootTypeMapper,
        ParameterMiddlewareInterface $parameterMapper,
        FieldMiddlewareInterface $fieldMiddleware,
        InputFieldMiddlewareInterface $inputFieldMiddleware
    ) {
        $this->annotationReader      = $annotationReader;
        $this->recursiveTypeMapper   = $typeMapper;
        $this->typeResolver          = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy        = $namingStrategy;
        $this->typeMapper            = new TypeHandler($argumentResolver, $rootTypeMapper, $typeResolver);
        $this->parameterMapper       = $parameterMapper;
        $this->fieldMiddleware = $fieldMiddleware;
        $this->inputFieldMiddleware = $inputFieldMiddleware;
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

    /**
     * @return array<string, FieldDefinition> QueryField indexed by name.
     */
    public function getFields(object $controller, ?string $typeName = null): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($controller, Annotations\Field::class, true, $typeName);

        $refClass = new ReflectionClass($controller);

        /** @var SourceFieldInterface[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        if ($controller instanceof FromSourceFieldsInterface) {
            $sourceFields = array_merge($sourceFields, $controller->getSourceFields());
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
     * @return array<InputField>
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
        $reflectors = array_merge($refClass->getProperties(), $refClass->getMethods(ReflectionMethod::IS_PUBLIC));
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
                array_fill_keys(array_keys($fields), $reflector)
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
    public function getSelfFields(string $className, ?string $typeName = null): array
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
     *
     * @return array<string, ParameterInterface> Returns an array of parameters.
     */
    public function getParameters(ReflectionMethod $refMethod): array
    {
        $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
        //$docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();

        $parameters = $refMethod->getParameters();

        return $this->mapParameters($parameters, $docBlockObj);
    }

    /**
     * @param ReflectionMethod $refMethod A method annotated with a Decorate annotation.
     *
     * @return array<string, ParameterInterface> Returns an array of parameters.
     */
    public function getParametersForDecorator(ReflectionMethod $refMethod): array
    {
        $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
        //$docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();

        $parameters = $refMethod->getParameters();

        if (empty($parameters)) {
            return [];
        }

        // Let's remove the first parameter.
        array_shift($parameters);

        return $this->mapParameters($parameters, $docBlockObj);
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
    private function getFieldsByAnnotations($controller, string $annotationName, bool $injectSource, ?string $typeName = null): array
    {
        $refClass = new ReflectionClass($controller);
        /** @var array<string, FieldDefinition> $queryList */
        $queryList = [];

        /** @var ReflectionMethod[]|ReflectionProperty[] $reflectorByFields */
        $reflectorByFields = [];

        $oldDeclaringClass = null;
        $context           = null;

        $closestMatchingTypeClass = null;
        if ($annotationName === Field::class) {
            $parent = get_parent_class($refClass->getName());
            if ($parent !== false) {
                $closestMatchingTypeClass = $this->recursiveTypeMapper->findClosestMatchingParent($parent);
            }
        }

        /** @var ReflectionProperty[]|ReflectionMethod[] $reflectors */
        $reflectors = array_merge($refClass->getProperties(), $refClass->getMethods(ReflectionMethod::IS_PUBLIC));
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
                array_fill_keys(array_keys($fields), $reflector)
            );

            $queryList = array_merge($queryList, $fields);
        }

        return $queryList;
    }

    /**
     * Gets fields by class method annotations.
     *
     * @param string|object                 $controller
     * @param class-string<AbstractRequest> $annotationName
     *
     * @return array<string, FieldDefinition>
     *
     * @throws AnnotationException
     */
    private function getFieldsByMethodAnnotations($controller, ReflectionClass $refClass, ReflectionMethod $refMethod, string $annotationName, bool $injectSource, ?string $typeName = null): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getMethodAnnotations($refMethod, $annotationName);
        foreach ($annotations as $queryAnnotation) {
            $description = null;
            $methodName = $refMethod->getName();

            if ($queryAnnotation instanceof Field) {
                if (strpos($methodName, 'set') === 0) {
                    continue;
                }
                $for = $queryAnnotation->getFor();
                if ($typeName && $for && ! in_array($typeName, $for)) {
                    continue;
                }

                $description = $queryAnnotation->getDescription();
            }

            $fieldDescriptor = new QueryFieldDescriptor();
            $fieldDescriptor->setRefMethod($refMethod);

            $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $fieldDescriptor->setDeprecationReason($this->getDeprecationReason($docBlockObj));


            $name       = $queryAnnotation->getName() ?: $this->namingStrategy->getFieldNameFromMethodName($methodName);

            if (! $description) {
                $description = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();
            }

            $fieldDescriptor->setName($name);
            $fieldDescriptor->setComment(trim($description));

            [$prefetchMethodName, $prefetchArgs, $prefetchRefMethod] = $this->getPrefetchMethodInfo($refClass, $refMethod, $queryAnnotation);
            if ($prefetchMethodName) {
                $fieldDescriptor->setPrefetchMethodName($prefetchMethodName);
                $fieldDescriptor->setPrefetchParameters($prefetchArgs);
            }

            $parameters = $refMethod->getParameters();
            if ($injectSource === true) {
                $firstParameter = array_shift($parameters);
                // TODO: check that $first_parameter type is correct.
            }
            if ($prefetchMethodName !== null && $prefetchRefMethod !== null) {
                $secondParameter = array_shift($parameters);
                if ($secondParameter === null) {
                    throw InvalidPrefetchMethodRuntimeException::prefetchDataIgnored($prefetchRefMethod, $injectSource);
                }
            }

            $args = $this->mapParameters($parameters, $docBlockObj);

            $fieldDescriptor->setParameters($args);

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
            if (is_string($controller)) {
                $fieldDescriptor->setTargetMethodOnSource($methodName);
            } else {
                $callable = [$controller, $methodName];
                Assert::isCallable($callable);
                $fieldDescriptor->setCallable($callable);
            }

            $fieldDescriptor->setType($type);
            $fieldDescriptor->setInjectSource($injectSource);

            $fieldDescriptor->setMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refMethod));

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
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
     * @param string|object                 $controller
     * @param class-string<AbstractRequest> $annotationName
     *
     * @return array<string, FieldDefinition>
     *
     * @throws AnnotationException
     */
    private function getFieldsByPropertyAnnotations($controller, ReflectionClass $refClass, ReflectionProperty $refProperty, string $annotationName, ?string $typeName = null): array
    {
        $fields = [];
        $annotations = $this->annotationReader->getPropertyAnnotations($refProperty, $annotationName);
        foreach ($annotations as $queryAnnotation) {
            $description = null;

            if ($queryAnnotation instanceof Field) {
                $for = $queryAnnotation->getFor();
                if ($typeName && $for && ! in_array($typeName, $for)) {
                    continue;
                }

                $description = $queryAnnotation->getDescription();
            }

            $fieldDescriptor = new QueryFieldDescriptor();
            $fieldDescriptor->setRefProperty($refProperty);

            $docBlock        = $this->cachedDocBlockFactory->getDocBlock($refProperty);
            $fieldDescriptor->setDeprecationReason($this->getDeprecationReason($docBlock));
            $name = $queryAnnotation->getName() ?: $refProperty->getName();

            if (! $description) {
                $description = $docBlock->getSummary() . PHP_EOL . $docBlock->getDescription()->render();

                /** @var Var_[] $varTags */
                $varTags = $docBlock->getTagsByName('var');
                $varTag = reset($varTags);
                if ($varTag) {
                    $description .= PHP_EOL . $varTag->getDescription();
                }
            }

            $fieldDescriptor->setName($name);
            $fieldDescriptor->setComment(trim($description));

            [$prefetchMethodName, $prefetchArgs] = $this->getPrefetchMethodInfo($refClass, $refProperty, $queryAnnotation);
            if ($prefetchMethodName) {
                $fieldDescriptor->setPrefetchMethodName($prefetchMethodName);
                $fieldDescriptor->setPrefetchParameters($prefetchArgs);
            }

            $outputType = $queryAnnotation->getOutputType();
            if ($outputType) {
                $type = $this->typeResolver->mapNameToOutputType($outputType);
            } else {
                $type = $this->typeMapper->mapPropertyType($refProperty, $docBlock, false);
                assert($type instanceof OutputType);
            }

            $fieldDescriptor->setType($type);
            $fieldDescriptor->setInjectSource(false);

            if (is_string($controller)) {
                $fieldDescriptor->setTargetPropertyOnSource($refProperty->getName());
            } else {
                $fieldDescriptor->setCallable(static function () use ($controller, $refProperty) {
                    return PropertyAccessor::getValue($controller, $refProperty->getName());
                });
            }

            $fieldDescriptor->setMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refProperty));

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
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

        $typeField       = $this->annotationReader->getTypeAnnotation($refClass);
        $extendTypeField = $this->annotationReader->getExtendTypeAnnotation($refClass);

        if ($typeField !== null) {
            $objectClass = $typeField->getClass();
        } elseif ($extendTypeField !== null) {
            $objectClass = $extendTypeField->getClass();
            if ($objectClass === null) {
                // We need to be able to fetch the mapped PHP class from the object type!
                $typeName = $extendTypeField->getName();
                Assert::notNull($typeName);
                $targetedType = $this->recursiveTypeMapper->mapNameToType($typeName);
                if (! $targetedType instanceof MutableObjectType) {
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
        $context           = null;
        $queryList         = [];

        foreach ($sourceFields as $sourceField) {
            $fieldDescriptor = new QueryFieldDescriptor();
            $fieldDescriptor->setName($sourceField->getName());

            if (! $sourceField->shouldFetchFromMagicProperty()) {
                try {
                    $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getSourceName() ?? $sourceField->getName());
                } catch (FieldNotFoundException $e) {
                    throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
                }
                $fieldDescriptor->setRefMethod($refMethod);
                $methodName = $refMethod->getName();
                $fieldDescriptor->setTargetMethodOnSource($methodName);

                $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
                $docBlockComment = rtrim($docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render());

                $deprecated      = $docBlockObj->getTagsByName('deprecated');
                if (count($deprecated) >= 1) {
                    $fieldDescriptor->setDeprecationReason(trim((string) $deprecated[0]));
                }

                $description = $sourceField->getDescription() ?? $docBlockComment;
                $fieldDescriptor->setComment($description);
                $args = $this->mapParameters($refMethod->getParameters(), $docBlockObj, $sourceField);

                $fieldDescriptor->setParameters($args);

                $outputType = $sourceField->getOutputType();
                $phpTypeStr = $sourceField->getPhpType();
                if ($outputType !== null) {
                    $type = $this->resolveOutputType($outputType, $refClass, $sourceField);
                } elseif ($phpTypeStr !== null) {
                    $type = $this->resolvePhpType($phpTypeStr, $refClass, $refMethod);
                } else {
                    $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
                }
            } else {
                $fieldDescriptor->setMagicProperty($sourceField->getSourceName() ?? $sourceField->getName());
                $fieldDescriptor->setComment($sourceField->getDescription());

                $outputType = $sourceField->getOutputType();
                if ($outputType !== null) {
                    $type = $this->resolveOutputType($outputType, $refClass, $sourceField);
                } else {
                    $phpTypeStr = $sourceField->getPhpType();
                    Assert::notNull($phpTypeStr);
                    $magicGefRefMethod = $this->getMagicGetMethodFromSourceClassOrProxy($refClass);

                    $type = $this->resolvePhpType($phpTypeStr, $refClass, $magicGefRefMethod);
                }
            }

            $fieldDescriptor->setType($type);
            $fieldDescriptor->setInjectSource(false);
            $fieldDescriptor->setMiddlewareAnnotations($sourceField->getMiddlewareAnnotations());

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
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
        if (! $sourceRefClass->hasMethod($magicGet)) {
            throw MissingMagicGetException::cannotFindMagicGet($sourceClassName);
        }

        return $sourceRefClass->getMethod($magicGet);
    }

    /**
     * @param ReflectionClass<object> $refClass
     *
     * @return OutputType&Type
     */
    private function resolveOutputType(string $outputType, ReflectionClass $refClass, SourceFieldInterface $sourceField): OutputType
    {
        try {
            return $this->typeResolver->mapNameToOutputType($outputType);
        } catch (CannotMapTypeExceptionInterface $e) {
            $e->addSourceFieldInfo($refClass, $sourceField);
            throw $e;
        }
    }

    /**
     * @param ReflectionClass<object> $refClass
     *
     * @return OutputType&Type
     */
    private function resolvePhpType(string $phpTypeStr, ReflectionClass $refClass, ReflectionMethod $refMethod): OutputType
    {
        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        $context = $this->cachedDocBlockFactory->getContextFromClass($refClass);
        $phpdocType = $typeResolver->resolve($phpTypeStr, $context);
        Assert::notNull($phpdocType);

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
            if (! $methodName) {
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
    private function mapParameters(array $refParameters, DocBlock $docBlock, ?SourceFieldInterface $sourceField = null): array
    {
        if (empty($refParameters)) {
            return [];
        }
        $additionalParameterAnnotations = $sourceField !== null ? $sourceField->getParameterAnnotations() : [];

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
            if (! empty($additionalParameterAnnotations[$parameter->getName()])) {
                $parameterAnnotations->merge($additionalParameterAnnotations[$parameter->getName()]);
                unset($additionalParameterAnnotations[$parameter->getName()]);
            }

            $parameterObj = $this->parameterMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotations, $this->typeMapper);

            $args[$parameter->getName()] = $parameterObj;
        }

        // Sanity check, are the parameters declared in $additionalParameterAnnotations available in $refParameters?
        if (! empty($additionalParameterAnnotations)) {
            $refParameter = reset($refParameters);
            foreach ($additionalParameterAnnotations as $parameterName => $parameterAnnotations) {
                foreach ($parameterAnnotations->getAllAnnotations() as $annotation) {
                    $refMethod = $refParameter->getDeclaringFunction();
                    assert($refMethod instanceof ReflectionMethod);
                    throw InvalidParameterException::parameterNotFoundFromSourceField($refParameter->getName(), get_class($annotation), $refMethod);
                }
            }
        }

        return $args;
    }

    /**
     * Extracts deprecation reason from doc block.
     */
    private function getDeprecationReason(DocBlock $docBlockObj): ?string
    {
        $deprecated = $docBlockObj->getTagsByName('deprecated');
        if (count($deprecated) >= 1) {
            return trim((string) $deprecated[0]);
        }

        return null;
    }

    /**
     * Extracts prefetch method info from annotation.
     *
     * @param ReflectionMethod|ReflectionProperty $reflector
     *
     * @return array{0: string|null, 1: array<mixed>, 2: ReflectionMethod|null}
     *
     * @throws InvalidArgumentException
     */
    private function getPrefetchMethodInfo(ReflectionClass $refClass, $reflector, object $annotation): array
    {
        $prefetchMethodName = null;
        $prefetchArgs = [];
        $prefetchRefMethod = null;

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
            }
        }

        return [$prefetchMethodName, $prefetchArgs, $prefetchRefMethod];
    }

    /**
     * Gets input fields by class method annotations.
     *
     * @param string|object $controller
     * @param class-string<AbstractRequest> $annotationName
     * @param array<mixed> $defaultProperties
     *
     * @return array<string, InputField>
     *
     * @throws AnnotationException
     */
    private function getInputFieldsByMethodAnnotations($controller, ReflectionClass $refClass, ReflectionMethod $refMethod, string $annotationName, bool $injectSource, array $defaultProperties, ?string $typeName = null, bool $isUpdate = false): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getMethodAnnotations($refMethod, $annotationName);
        foreach ($annotations as $fieldAnnotations) {
            $description = null;
            if ($fieldAnnotations instanceof Field) {
                $for = $fieldAnnotations->getFor();
                if ($typeName && $for && !in_array($typeName, $for)) {
                    continue;
                }
                $description = $fieldAnnotations->getDescription();

                $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
                $methodName = $refMethod->getName();
                if (strpos($methodName, 'set') !== 0) {
                    continue;
                }
                $name = $fieldAnnotations->getName() ?: $this->namingStrategy->getInputFieldNameFromMethodName($methodName);
                if (!$description) {
                    $description = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();
                }

                $inputFieldDescriptor = new InputFieldDescriptor();
                $inputFieldDescriptor->setRefMethod($refMethod);
                $inputFieldDescriptor->setIsUpdate($isUpdate);
                $inputFieldDescriptor->setName($name);
                $inputFieldDescriptor->setComment(trim($description));

                $parameters = $refMethod->getParameters();
                if ($injectSource === true) {
                    $firstParameter = array_shift($parameters);
                    // TODO: check that $first_parameter type is correct.
                }

                /** @var array<string, InputTypeParameterInterface> $args */
                $args = $this->mapParameters($parameters, $docBlockObj);

                $inputFieldDescriptor->setParameters($args);

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

                $inputFieldDescriptor->setHasDefaultValue($isUpdate);
                $inputFieldDescriptor->setDefaultValue($args[$name]->getDefaultValue());
                $constructerParameters = $this->getClassConstructParameterNames($refClass);
                if (!in_array($name, $constructerParameters)) {
                    $inputFieldDescriptor->setTargetMethodOnSource($methodName);
                }

                $inputFieldDescriptor->setType($type);
                $inputFieldDescriptor->setInjectSource($injectSource);

                $inputFieldDescriptor->setMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refMethod));

                $field = $this->inputFieldMiddleware->process($inputFieldDescriptor, new class implements InputFieldHandlerInterface {
                    public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField
                    {
                        return InputField::fromFieldDescriptor($inputFieldDescriptor);
                    }
                });

                if ($field === null) {
                    continue;
                }

                $fields[$name] = $field;
            }
        }

        return $fields;
    }

    /**
     * Gets input fields by class property annotations.
     *
     * @param string|object $controller
     * @param class-string<AbstractRequest> $annotationName
     * @param array<mixed> $defaultProperties
     *
     * @return array<string, InputField>
     *
     * @throws AnnotationException
     */
    private function getInputFieldsByPropertyAnnotations($controller, ReflectionClass $refClass, ReflectionProperty $refProperty, string $annotationName, array $defaultProperties, ?string $typeName = null, bool $isUpdate = false): array
    {
        $fields = [];

        $annotations = $this->annotationReader->getPropertyAnnotations($refProperty, $annotationName);
        $docBlock = $this->cachedDocBlockFactory->getDocBlock($refProperty);
        foreach ($annotations as $annotation) {
            $description = null;

            if ($annotation instanceof Field) {
                $for = $annotation->getFor();
                if ($typeName && $for && !in_array($typeName, $for)) {
                    continue;
                }

                $description = $annotation->getDescription();
                $name = $annotation->getName() ?: $refProperty->getName();
                $inputType = $annotation->getInputType();
                $constructerParameters = $this->getClassConstructParameterNames($refClass);
                $inputProperty = $this->typeMapper->mapInputProperty($refProperty, $docBlock, $name, $inputType, $defaultProperties[$refProperty->getName()] ?? null, $isUpdate ? true : null);

                if (! $description) {
                    $description = $inputProperty->getDescription();
                }

                if (in_array($name, $constructerParameters)) {
                    $middlewareAnnotations = $this->annotationReader->getPropertyAnnotations($refProperty, MiddlewareAnnotationInterface::class);
                    if ($middlewareAnnotations !== []){
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
                        $inputProperty->getDefaultValue()
                    );
                } else {
                    // setters and properties
                    $inputFieldDescriptor = new InputFieldDescriptor();
                    $inputFieldDescriptor->setRefProperty($refProperty);
                    $inputFieldDescriptor->setIsUpdate($isUpdate);
                    $inputFieldDescriptor->setHasDefaultValue($inputProperty->hasDefaultValue());
                    $inputFieldDescriptor->setDefaultValue($inputProperty->getDefaultValue());

                    $inputFieldDescriptor->setName($inputProperty->getName());
                    $inputFieldDescriptor->setComment(trim($description));

                    $inputFieldDescriptor->setParameters([$inputProperty->getName() => $inputProperty]);

                    $type = $inputProperty->getType();
                    if (!$inputType && $isUpdate && $type instanceof NonNull) {
                        $type = $type->getWrappedType();
                    }

                    $inputFieldDescriptor->setType($type);
                    $inputFieldDescriptor->setInjectSource(false);
                    $inputFieldDescriptor->setTargetPropertyOnSource($refProperty->getName());
                    $inputFieldDescriptor->setMiddlewareAnnotations($this->annotationReader->getMiddlewareAnnotations($refProperty));

                    $field = $this->inputFieldMiddleware->process($inputFieldDescriptor, new class implements InputFieldHandlerInterface {
                        public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField
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
        }

        return $fields;
    }


    /**
     * @return string[]
     */
    private function getClassConstructParameterNames(ReflectionClass $refClass): array
    {
        $constructor = $refClass->getConstructor();

        if (! $constructor) {
            return [];
        }

        $names = [];
        foreach ($constructor->getParameters() as $parameter) {
            $names[] = $parameter->getName();
        }

        return $names;
    }
}
