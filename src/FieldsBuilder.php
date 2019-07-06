<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\TypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use Webmozart\Assert\Assert;
use function array_merge;
use function array_shift;
use function get_parent_class;
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
    /** @var TypeMapper */
    private $typeMapper;
    /** @var ParameterMapperInterface */
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
        ParameterMapperInterface $parameterMapper,
        FieldMiddlewareInterface $fieldMiddleware
    ) {
        $this->annotationReader      = $annotationReader;
        $this->recursiveTypeMapper   = $typeMapper;
        $this->typeResolver          = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy        = $namingStrategy;
        $this->typeMapper            = new TypeMapper($typeMapper, $argumentResolver, $rootTypeMapper, $typeResolver);
        $this->parameterMapper       = $parameterMapper;
        $this->fieldMiddleware = $fieldMiddleware;
    }

    /**
     * @return FieldDefinition[]
     *
     * @throws ReflectionException
     */
    public function getQueries(object $controller): array
    {
        return $this->getFieldsByAnnotations($controller, Query::class, false);
    }

    /**
     * @return FieldDefinition[]
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

        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        if ($controller instanceof FromSourceFieldsInterface) {
            $sourceFields = array_merge($sourceFields, $controller->getSourceFields());
        }

        $fieldsFromSourceFields = $this->getQueryFieldsFromSourceFields($sourceFields, $refClass);

        $fields = [];
        foreach ($fieldAnnotations as $field) {
            $fields[$field->name] = $field;
        }
        foreach ($fieldsFromSourceFields as $field) {
            $fields[$field->name] = $field;
        }

        return $fields;
    }

    /**
     * Track Field annotation in a self targeted type
     *
     * @return array<string, FieldDefinition> QueryField indexed by name.
     */
    public function getSelfFields(string $className): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations(null, Annotations\Field::class, false, $className);

        $refClass = new ReflectionClass($className);

        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        $fieldsFromSourceFields = $this->getQueryFieldsFromSourceFields($sourceFields, $refClass);

        $fields = [];
        foreach ($fieldAnnotations as $field) {
            $fields[$field->name] = $field;
        }
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
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field (unless @Type has no class attribute), false for @Query and @Mutation
     *
     * @return FieldDefinition[]
     *
     * @throws CannotMapTypeException
     * @throws ReflectionException
     */
    private function getFieldsByAnnotations(?object $controller, string $annotationName, bool $injectSource, ?string $sourceClassName = null): array
    {
        if ($sourceClassName !== null) {
            $refClass = new ReflectionClass($sourceClassName);
        } else {
            $refClass = new ReflectionClass($controller);
        }

        $queryList = [];

        $oldDeclaringClass = null;
        $context           = null;

        $closestMatchingTypeClass = null;
        if ($annotationName === Field::class) {
            $parent = get_parent_class($refClass->getName());
            if ($parent !== false) {
                $closestMatchingTypeClass = $this->recursiveTypeMapper->findClosestMatchingParent($parent);
            }
        }

        foreach ($refClass->getMethods() as $refMethod) {
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
                        throw InvalidPrefetchMethodException::methodNotFound($refMethod, $refClass, $prefetchMethodName, $e);
                    }

                    $prefetchParameters = $prefetchRefMethod->getParameters();
                    $first_prefetch_parameter = array_shift($prefetchParameters);

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
                    throw InvalidPrefetchMethodException::prefetchDataIgnored($prefetchRefMethod, $injectSource);
                }
            }

            $args = $this->mapParameters($parameters, $docBlockObj);

            $fieldDescriptor->setParameters($args);

            $outputType = $queryAnnotation->getOutputType();
            if ($outputType) {
                try {
                    $type = $this->typeResolver->mapNameToOutputType($outputType);
                } catch (CannotMapTypeExceptionInterface $e) {
                    throw CannotMapTypeException::wrapWithReturnInfo($e, $refMethod);
                }
            } else {
                $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
            }
            if ($sourceClassName !== null) {
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
                    if ($fieldDescriptor->getTargetMethodOnSource() !== null) {
                        return QueryField::selfField($fieldDescriptor);
                    }

                    return QueryField::externalField($fieldDescriptor);
                }
            });

            if ($field !== null) {
                $queryList[] = $field;
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
                $targetedType = $this->recursiveTypeMapper->mapNameToType($extendTypeField->getName());
                if (!$targetedType instanceof TypeAnnotatedObjectType) {
                    return [];
                }
                $objectClass = $targetedType->getMappedClassName();
            }
        } else {
            throw MissingAnnotationException::missingTypeExceptionToUseSourceField();
        }

        $objectRefClass = new ReflectionClass($objectClass);

        $oldDeclaringClass = null;
        $context           = null;
        $queryList         = [];

        foreach ($sourceFields as $sourceField) {
            try {
                $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getName());
            } catch (FieldNotFoundException $e) {
                throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
            }

            $fieldDescriptor = new QueryFieldDescriptor();
            $fieldDescriptor->setName($sourceField->getName());

            $methodName = $refMethod->getName();

            $fieldDescriptor->setTargetMethodOnSource($methodName);

            $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $docBlockComment = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();

            $fieldDescriptor->setComment($docBlockComment);

            $args = $this->mapParameters($refMethod->getParameters(), $docBlockObj);

            $fieldDescriptor->setParameters($args);

            $outputType = $sourceField->getOutputType();
            if ($outputType) {
                try {
                    $type = $this->typeResolver->mapNameToOutputType($outputType);
                } catch (CannotMapTypeExceptionInterface $e) {
                    throw CannotMapTypeException::wrapWithSourceField($e, $refClass, $sourceField);
                }
            } else {
                $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
            }

            $fieldDescriptor->setType($type);
            $fieldDescriptor->setMiddlewareAnnotations($sourceField->getAnnotations());

            $field = $this->fieldMiddleware->process($fieldDescriptor, new class implements FieldHandlerInterface {
                public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
                {
                    return QueryField::selfField($fieldDescriptor);
                }
            });

            if ($field === null) {
                continue;
            }

            $queryList[] = $field;
        }

        return $queryList;
    }

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
     *
     * @throws MissingTypeHintException
     */
    private function mapParameters(array $refParameters, DocBlock $docBlock): array
    {
        $args = [];

        $docBlockTypes = [];
        if (! empty($refParameters)) {
            /** @var DocBlock\Tags\Param[] $paramTags */
            $paramTags = $docBlock->getTagsByName('param');
            foreach ($paramTags as $paramTag) {
                $docBlockTypes[$paramTag->getVariableName()] = $paramTag->getType();
            }
        }

        foreach ($refParameters as $parameter) {
            $parameterAnnotations = $this->annotationReader->getParameterAnnotations($parameter);

            $parameterObj = $this->parameterMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotations);

            if ($parameterObj === null) {
                $parameterObj = $this->typeMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotations);
            }
            $args[$parameter->getName()] = $parameterObj;
        }

        return $args;
    }
}
