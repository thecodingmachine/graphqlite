<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\InvalidParameterException;
use TheCodingMachine\GraphQLite\Annotations\Field;
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
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use Webmozart\Assert\Assert;
use function array_merge;
use function array_shift;
use function assert;
use function get_class;
use function get_parent_class;
use function is_string;
use function reset;
use function ucfirst;

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

    public function __construct(
        AnnotationReader $annotationReader,
        RecursiveTypeMapperInterface $typeMapper,
        ArgumentResolver $argumentResolver,
        TypeResolver $typeResolver,
        CachedDocBlockFactory $cachedDocBlockFactory,
        NamingStrategyInterface $namingStrategy,
        RootTypeMapperInterface $rootTypeMapper,
        ParameterMiddlewareInterface $parameterMapper,
        FieldMiddlewareInterface $fieldMiddleware
    ) {
        $this->annotationReader      = $annotationReader;
        $this->recursiveTypeMapper   = $typeMapper;
        $this->typeResolver          = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy        = $namingStrategy;
        $this->typeMapper            = new TypeHandler($argumentResolver, $rootTypeMapper, $typeResolver);
        $this->parameterMapper       = $parameterMapper;
        $this->fieldMiddleware = $fieldMiddleware;
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
    public function getFields(object $controller): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($controller, Annotations\Field::class, true);

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
     * Track Field annotation in a self targeted type
     *
     * @param class-string<object> $className
     *
     * @return array<string, FieldDefinition> QueryField indexed by name.
     */
    public function getSelfFields(string $className): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($className, Annotations\Field::class, false);

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
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field (unless @Type has no class attribute), false for @Query and @Mutation
     *
     * @return array<string, FieldDefinition>
     *
     * @throws ReflectionException
     */
    private function getFieldsByAnnotations($controller, string $annotationName, bool $injectSource): array
    {
        $refClass = new ReflectionClass($controller);

        $queryList = [];
        /** @var array<string, ReflectionMethod> $refMethodByFields */
        $refMethodByFields = [];

        $oldDeclaringClass = null;
        $context           = null;

        $closestMatchingTypeClass = null;
        if ($annotationName === Field::class) {
            $parent = get_parent_class($refClass->getName());
            if ($parent !== false) {
                $closestMatchingTypeClass = $this->recursiveTypeMapper->findClosestMatchingParent($parent);
            }
        }

        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
            if ($closestMatchingTypeClass !== null && $closestMatchingTypeClass === $refMethod->getDeclaringClass()->getName()) {
                // Optimisation: no need to fetch annotations from parent classes that are ALREADY GraphQL types.
                // We will merge the fields anyway.
                break;
            }

            // First, let's check the "Query" or "Mutation" or "Field" annotation
            $queryAnnotation = $this->annotationReader->getRequestAnnotation($refMethod, $annotationName);

            if ($queryAnnotation === null) {
                continue;
            }

            $fieldDescriptor = new QueryFieldDescriptor();
            $fieldDescriptor->setRefMethod($refMethod);

            $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $docBlockComment = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();

            $methodName = $refMethod->getName();
            $name       = $queryAnnotation->getName() ?: $this->namingStrategy->getFieldNameFromMethodName($methodName);

            $fieldDescriptor->setName($name);
            $fieldDescriptor->setComment($docBlockComment);

            // Get parameters from the prefetchMethod method if any.
            $prefetchMethodName = null;
            $prefetchArgs = [];
            if ($queryAnnotation instanceof Field) {
                $prefetchMethodName = $queryAnnotation->getPrefetchMethod();
                if ($prefetchMethodName !== null) {
                    $fieldDescriptor->setPrefetchMethodName($prefetchMethodName);
                    try {
                        $prefetchRefMethod = $refClass->getMethod($prefetchMethodName);
                    } catch (ReflectionException $e) {
                        throw InvalidPrefetchMethodRuntimeException::methodNotFound($refMethod, $refClass, $prefetchMethodName, $e);
                    }

                    $prefetchParameters = $prefetchRefMethod->getParameters();
                    $firstPrefetchParameter = array_shift($prefetchParameters);

                    $prefetchDocBlockObj = $this->cachedDocBlockFactory->getDocBlock($prefetchRefMethod);

                    $prefetchArgs = $this->mapParameters($prefetchParameters, $prefetchDocBlockObj);
                    $fieldDescriptor->setPrefetchParameters($prefetchArgs);
                }
            }

            $parameters = $refMethod->getParameters();
            if ($injectSource === true) {
                $firstParameter = array_shift($parameters);
                // TODO: check that $first_parameter type is correct.
            }
            if ($prefetchMethodName !== null) {
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

            if ($field !== null) {
                if (isset($refMethodByFields[$name])) {
                    throw DuplicateMappingException::createForQuery($refClass->getName(), $name, $refMethodByFields[$name], $refMethod);
                }
                $queryList[$name] = $field;
                $refMethodByFields[$name] = $refMethod;
            }

            /*if ($unauthorized) {
                $failWithValue = $failWith->getValue();
                $queryList[]   = QueryField::alwaysReturn($fieldDescriptor, $failWithValue);
            } elseif ($sourceClassName !== null) {
                $fieldDescriptor->setTargetMethodOnSource($methodName);
                $queryList[] = QueryField::selfField($fieldDescriptor);
            } else {
                $fieldDescriptor->setCallable([$controller, $methodName]);
                $queryList[] = QueryField::externalField($fieldDescriptor);
            }*/
        }

        return $queryList;
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
                    $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getName());
                } catch (FieldNotFoundException $e) {
                    throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
                }
                $fieldDescriptor->setRefMethod($refMethod);
                $methodName = $refMethod->getName();
                $fieldDescriptor->setTargetMethodOnSource($methodName);

                $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
                $docBlockComment = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();

                $fieldDescriptor->setComment($docBlockComment);
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
                $fieldDescriptor->setMagicProperty($sourceField->getName());
                $outputType = $sourceField->getOutputType();
                if ($outputType !== null) {
                    $type = $this->resolveOutputType($outputType, $refClass, $sourceField);
                } else {
                    $phpTypeStr = $sourceField->getPhpType();
                    Assert::notNull($phpTypeStr);
                    $refMethod = $refClass->getMethod('__get');
                    $type = $this->resolvePhpType($phpTypeStr, $refClass, $refMethod);
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
            $upperCasePropertyName = ucfirst($propertyName);
            if ($reflectionClass->hasMethod('get' . $upperCasePropertyName)) {
                $methodName = 'get' . $upperCasePropertyName;
            } elseif ($reflectionClass->hasMethod('is' . $upperCasePropertyName)) {
                $methodName = 'is' . $upperCasePropertyName;
            } else {
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
        if (! empty($refParameters)) {
            /** @var DocBlock\Tags\Param[] $paramTags */
            $paramTags = $docBlock->getTagsByName('param');
            foreach ($paramTags as $paramTag) {
                $docBlockTypes[$paramTag->getVariableName()] = $paramTag->getType();
            }
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
}
