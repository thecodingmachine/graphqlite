<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type as GraphQLType;
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
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
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
    /** @var AuthenticationServiceInterface */
    private $authenticationService;
    /** @var AuthorizationServiceInterface */
    private $authorizationService;
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

    public function __construct(
        AnnotationReader $annotationReader,
        RecursiveTypeMapperInterface $typeMapper,
        ArgumentResolver $argumentResolver,
        AuthenticationServiceInterface $authenticationService,
        AuthorizationServiceInterface $authorizationService,
        TypeResolver $typeResolver,
        CachedDocBlockFactory $cachedDocBlockFactory,
        NamingStrategyInterface $namingStrategy,
        RootTypeMapperInterface $rootTypeMapper,
        ParameterMapperInterface $parameterMapper
    ) {
        $this->annotationReader      = $annotationReader;
        $this->recursiveTypeMapper   = $typeMapper;
        $this->authenticationService = $authenticationService;
        $this->authorizationService  = $authorizationService;
        $this->typeResolver          = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy        = $namingStrategy;
        $this->typeMapper            = new TypeMapper($typeMapper, $argumentResolver, $rootTypeMapper, $typeResolver);
        $this->parameterMapper       = $parameterMapper;
    }

    // TODO: Add RecursiveTypeMapper in the list of parameters for getQueries and REMOVE the ControllerQueryProviderFactory.

    /**
     * @return QueryField[]
     *
     * @throws ReflectionException
     */
    public function getQueries(object $controller): array
    {
        return $this->getFieldsByAnnotations($controller, Query::class, false);
    }

    /**
     * @return QueryField[]
     *
     * @throws ReflectionException
     */
    public function getMutations(object $controller): array
    {
        return $this->getFieldsByAnnotations($controller, Mutation::class, false);
    }

    /**
     * @return array<string, QueryField> QueryField indexed by name.
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
     * @return array<string, QueryField> QueryField indexed by name.
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
     * @return QueryField[]
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

            $unauthorized = false;
            if (! $this->isAuthorized($refMethod)) {
                $failWith = $this->annotationReader->getFailWithAnnotation($refMethod);
                if ($failWith === null) {
                    continue;
                }
                $unauthorized = true;
            }

            $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $docBlockComment = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();

            $methodName = $refMethod->getName();
            $name       = $queryAnnotation->getName() ?: $this->namingStrategy->getFieldNameFromMethodName($methodName);

            // Get parameters from the prefetchMethod method if any.
            $prefetchMethodName = null;
            $prefetchArgs = [];
            if ($queryAnnotation instanceof Field) {
                $prefetchMethodName = $queryAnnotation->getPrefetchMethod();
                if ($prefetchMethodName !== null) {
                    try {
                        $prefetchRefMethod = $refClass->getMethod($prefetchMethodName);
                    } catch (ReflectionException $e) {
                        throw InvalidPrefetchMethodException::methodNotFound($refMethod, $refClass, $prefetchMethodName, $e);
                    }

                    $prefetchParameters = $prefetchRefMethod->getParameters();
                    $first_prefetch_parameter = array_shift($prefetchParameters);

                    $prefetchDocBlockObj = $this->cachedDocBlockFactory->getDocBlock($prefetchRefMethod);

                    $prefetchArgs = $this->mapParameters($prefetchParameters, $prefetchDocBlockObj);
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

            if ($queryAnnotation->getOutputType()) {
                try {
                    $type = $this->typeResolver->mapNameToOutputType($queryAnnotation->getOutputType());
                } catch (CannotMapTypeExceptionInterface $e) {
                    throw CannotMapTypeException::wrapWithReturnInfo($e, $refMethod);
                }
            } else {
                $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
            }

            if ($unauthorized) {
                $failWithValue = $failWith->getValue();
                $queryList[]   = QueryField::alwaysReturn($name, $type, $args, $failWithValue, $docBlockComment);
            } elseif ($sourceClassName !== null) {
                $queryList[] = QueryField::selfField($name, $type, $args, $methodName, $docBlockComment, $prefetchMethodName, $prefetchArgs);
            } else {
                $queryList[] = QueryField::externalField($name, $type, $args, [$controller, $methodName], $docBlockComment, $injectSource, $prefetchMethodName, $prefetchArgs);
            }
        }

        return $queryList;
    }

    /**
     * @param array<int, SourceFieldInterface> $sourceFields
     *
     * @return QueryField[]
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
        } else {
            throw MissingAnnotationException::missingTypeExceptionToUseSourceField();
        }

        $objectRefClass = new ReflectionClass($objectClass);

        $oldDeclaringClass = null;
        $context           = null;
        $queryList         = [];

        foreach ($sourceFields as $sourceField) {
            // Ignore the field if we must be logged.
            $right        = $sourceField->getRight();
            $unauthorized = false;
            if (($sourceField->isLogged() && ! $this->authenticationService->isLogged())
                || ($right !== null && ! $this->authorizationService->isAllowed($right->getName()))) {
                if (! $sourceField->canFailWith()) {
                    continue;
                }

                $unauthorized = true;
            }

            try {
                $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getName());
            } catch (FieldNotFoundException $e) {
                throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
            }

            $methodName = $refMethod->getName();

            $docBlockObj     = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $docBlockComment = $docBlockObj->getSummary() . "\n" . $docBlockObj->getDescription()->render();

            $args = $this->mapParameters($refMethod->getParameters(), $docBlockObj);

            if ($sourceField->isId()) {
                $type = GraphQLType::id();
                if (! $refMethod->getReturnType()->allowsNull()) {
                    $type = GraphQLType::nonNull($type);
                }
            } elseif ($sourceField->getOutputType()) {
                try {
                    $type = $this->typeResolver->mapNameToOutputType($sourceField->getOutputType());
                } catch (CannotMapTypeExceptionInterface $e) {
                    throw CannotMapTypeException::wrapWithSourceField($e, $refClass, $sourceField);
                }
            } else {
                $type = $this->typeMapper->mapReturnType($refMethod, $docBlockObj);
            }

            if (! $unauthorized) {
                $queryList[] = QueryField::selfField($sourceField->getName(), $type, $args, $methodName, $docBlockComment, null, []);
            } else {
                $failWithValue = $sourceField->getFailWith();
                $queryList[]   = QueryField::alwaysReturn($sourceField->getName(), $type, $args, $failWithValue, $docBlockComment);
            }
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
     * Checks the @Logged and @Right annotations.
     */
    private function isAuthorized(ReflectionMethod $reflectionMethod): bool
    {
        $loggedAnnotation = $this->annotationReader->getLoggedAnnotation($reflectionMethod);

        if ($loggedAnnotation !== null && ! $this->authenticationService->isLogged()) {
            return false;
        }

        $rightAnnotation = $this->annotationReader->getRightAnnotation($reflectionMethod);

        return $rightAnnotation === null || $this->authorizationService->isAllowed($rightAnnotation->getName());
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
            $parameterAnnotation = $this->annotationReader->getParameterAnnotation($parameter);

            $parameterObj = $this->parameterMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotation);

            if ($parameterObj === null) {
                $parameterObj = $this->typeMapper->mapParameter($parameter, $docBlock, $docBlockTypes[$parameter->getName()] ?? null, $parameterAnnotation);
            }
            $args[$parameter->getName()] = $parameterObj;
        }

        return $args;
    }
}
