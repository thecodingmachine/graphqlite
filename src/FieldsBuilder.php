<?php


namespace TheCodingMachine\GraphQL\Controllers;

use function array_merge;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Self_;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use TheCodingMachine\GraphQL\Controllers\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Types\UnionType;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Integer;
use ReflectionClass;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Reflection\CommentParser;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Types\DateTimeType;
use GraphQL\Type\Definition\Type as GraphQLType;

/**
 * A class in charge if returning list of fields for queries / mutations / entities / input types
 */
class FieldsBuilder
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $typeMapper;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;
    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;
    /**
     * @var ContainerInterface
     */
    private $registry;
    /**
     * @var CachedDocBlockFactory
     */
    private $cachedDocBlockFactory;

    /**
     * @param AnnotationReader $annotationReader
     * @param RecursiveTypeMapperInterface $typeMapper
     * @param HydratorInterface $hydrator
     * @param AuthenticationServiceInterface $authenticationService
     * @param AuthorizationServiceInterface $authorizationService
     * @param ContainerInterface $registry The registry is used to fetch custom return types by container identifier (using the returnType parameter of the Type annotation)
     */
    public function __construct(AnnotationReader $annotationReader, RecursiveTypeMapperInterface $typeMapper,
                                HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService,
                                AuthorizationServiceInterface $authorizationService, ContainerInterface $registry,
                                CachedDocBlockFactory $cachedDocBlockFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->typeMapper = $typeMapper;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->registry = $registry;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
    }

    // TODO: Add RecursiveTypeMapper in the list of parameters for getQueries and REMOVE the ControllerQueryProviderFactory.

    /**
     * @param object $controller
     * @return QueryField[]
     * @throws \ReflectionException
     */
    public function getQueries($controller): array
    {
        return $this->getFieldsByAnnotations($controller,Query::class, false);
    }

    /**
     * @param object $controller
     * @return QueryField[]
     * @throws \ReflectionException
     */
    public function getMutations($controller): array
    {
        return $this->getFieldsByAnnotations($controller,Mutation::class, false);
    }

    /**
     * @return array<string, QueryField> QueryField indexed by name.
     */
    public function getFields($controller): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations($controller, Annotations\Field::class, true);
        $sourceFields = $this->getSourceFields($controller);

        $fields = [];
        foreach ($fieldAnnotations as $field) {
            $fields[$field->name] = $field;
        }
        foreach ($sourceFields as $field) {
            $fields[$field->name] = $field;
        }

        return $fields;
    }

    /**
     * @param ReflectionMethod $refMethod A method annotated with a Factory annotation.
     * @return array<string, array<int, mixed>> Returns an array of fields as accepted by the InputObjectType constructor.
     */
    public function getInputFields(ReflectionMethod $refMethod): array
    {
        $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
        //$docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();

        $parameters = $refMethod->getParameters();

        $args = $this->mapParameters($parameters, $docBlockObj);

        return $args;
    }

    /**
     * @param object $controller
     * @param string $annotationName
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field, false for @Query and @Mutation
     * @return QueryField[]
     * @throws CannotMapTypeException
     * @throws \ReflectionException
     */
    private function getFieldsByAnnotations($controller, string $annotationName, bool $injectSource): array
    {
        $refClass = new \ReflectionClass($controller);

        $queryList = [];

        $oldDeclaringClass = null;
        $context = null;

        foreach ($refClass->getMethods() as $refMethod) {
            // First, let's check the "Query" or "Mutation" or "Field" annotation
            $queryAnnotation = $this->annotationReader->getRequestAnnotation($refMethod, $annotationName);

            if ($queryAnnotation !== null) {
                if (!$this->isAuthorized($refMethod)) {
                    continue;
                }

                $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
                $docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();

                $methodName = $refMethod->getName();
                $name = $queryAnnotation->getName() ?: $methodName;

                $parameters = $refMethod->getParameters();
                if ($injectSource === true) {
                    $first_parameter = array_shift($parameters);
                    // TODO: check that $first_parameter type is correct.
                }

                $args = $this->mapParameters($parameters, $docBlockObj);

                if ($queryAnnotation->getReturnType()) {
                    $type = $this->registry->get($queryAnnotation->getReturnType());
                    if (!$type instanceof OutputType) {
                        throw new \InvalidArgumentException(sprintf("In %s::%s, the 'returnType' parameter in @Type annotation should contain a container identifier that points to an entry that implements GraphQL\\Type\\Definition\\OutputType. The '%s' container entry does not implement GraphQL\\Type\\Definition\\OutputType", $refMethod->getDeclaringClass()->getName(), $refMethod->getName(), $queryAnnotation->getReturnType()));
                    }
                } else {
                    $type = $this->mapReturnType($refMethod, $docBlockObj);
                }

                $queryList[] = new QueryField($name, $type, $args, [$controller, $methodName], null, $this->hydrator, $docBlockComment, $injectSource);
            }
        }

        return $queryList;
    }

    /**
     * @return GraphQLType&OutputType
     */
    private function mapReturnType(ReflectionMethod $refMethod, DocBlock $docBlockObj): GraphQLType
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType !== null) {
            $typeResolver = new \phpDocumentor\Reflection\TypeResolver();
            $phpdocType = $typeResolver->resolve((string) $returnType);
            $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
        } else {
            $phpdocType = new Mixed_();
        }

        $docBlockReturnType = $this->getDocBlocReturnType($docBlockObj, $refMethod);

        try {
            /** @var GraphQLType&OutputType $type */
            $type = $this->mapType($phpdocType, $docBlockReturnType, $returnType ? $returnType->allowsNull() : false, false);
        } catch (TypeMappingException $e) {
            throw TypeMappingException::wrapWithReturnInfo($e, $refMethod);
        } catch (CannotMapTypeException $e) {
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
     * @param object $controller
     * @return QueryField[]
     * @throws CannotMapTypeException
     * @throws \ReflectionException
     */
    private function getSourceFields($controller): array
    {
        $refClass = new \ReflectionClass($controller);

        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        if ($controller instanceof FromSourceFieldsInterface) {
            $sourceFields = array_merge($sourceFields, $controller->getSourceFields());
        }

        if (empty($sourceFields)) {
            return [];
        }

        $typeField = $this->annotationReader->getTypeAnnotation($refClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeExceptionToUseSourceField();
        }

        $objectClass = $typeField->getClass();
        $objectRefClass = new \ReflectionClass($objectClass);

        $oldDeclaringClass = null;
        $context = null;
        $queryList = [];

        foreach ($sourceFields as $sourceField) {
            // Ignore the field if we must be logged.
            if ($sourceField->isLogged() && !$this->authenticationService->isLogged()) {
                continue;
            }

            $right = $sourceField->getRight();
            if ($right !== null && !$this->authorizationService->isAllowed($right->getName())) {
                continue;
            }

            try {
                $refMethod = $this->getMethodFromPropertyName($objectRefClass, $sourceField->getName());
            } catch (FieldNotFoundException $e) {
                throw FieldNotFoundException::wrapWithCallerInfo($e, $refClass->getName());
            }

            $methodName = $refMethod->getName();


            $docBlockObj = $this->cachedDocBlockFactory->getDocBlock($refMethod);
            $docBlockComment = $docBlockObj->getSummary()."\n".$docBlockObj->getDescription()->render();


            $args = $this->mapParameters($refMethod->getParameters(), $docBlockObj);

            if ($sourceField->isId()) {
                $type = GraphQLType::id();
                if (!$refMethod->getReturnType()->allowsNull()) {
                    $type = GraphQLType::nonNull($type);
                }
            } elseif ($sourceField->getReturnType()) {
                $type = $this->registry->get($sourceField->getReturnType());
            } else {
                $type = $this->mapReturnType($refMethod, $docBlockObj);
            }

            $queryList[] = new QueryField($sourceField->getName(), $type, $args, null, $methodName, $this->hydrator, $docBlockComment, false);

        }
        return $queryList;
    }

    private function getMethodFromPropertyName(\ReflectionClass $reflectionClass, string $propertyName): \ReflectionMethod
    {
        if ($reflectionClass->hasMethod($propertyName)) {
            $methodName = $propertyName;
        } else {
            $upperCasePropertyName = \ucfirst($propertyName);
            if ($reflectionClass->hasMethod('get'.$upperCasePropertyName)) {
                $methodName = 'get'.$upperCasePropertyName;
            } elseif ($reflectionClass->hasMethod('is'.$upperCasePropertyName)) {
                $methodName = 'is'.$upperCasePropertyName;
            } else {
                throw FieldNotFoundException::missingField($reflectionClass->getName(), $propertyName);
            }
        }

        return $reflectionClass->getMethod($methodName);
    }

    /**
     * Checks the @Logged and @Right annotations.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @return bool
     */
    private function isAuthorized(\ReflectionMethod $reflectionMethod) : bool
    {
        $loggedAnnotation = $this->annotationReader->getLoggedAnnotation($reflectionMethod);

        if ($loggedAnnotation !== null && !$this->authenticationService->isLogged()) {
            return false;
        }


        $rightAnnotation = $this->annotationReader->getRightAnnotation($reflectionMethod);

        if ($rightAnnotation !== null && !$this->authorizationService->isAllowed($rightAnnotation->getName())) {
            return false;
        }

        return true;
    }

    /**
     * Note: there is a bug in $refMethod->allowsNull that forces us to use $standardRefMethod->allowsNull instead.
     *
     * @param \ReflectionParameter[] $refParameters
     * @return array[] An array of ['type'=>Type, 'defaultValue'=>val]
     * @throws MissingTypeHintException
     */
    private function mapParameters(array $refParameters, DocBlock $docBlock): array
    {
        $args = [];

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        foreach ($refParameters as $parameter) {
            $parameterType = $parameter->getType();
            $allowsNull = $parameterType === null ? true : $parameterType->allowsNull();

            $type = (string) $parameterType;
            if ($type === '') {
                throw MissingTypeHintException::missingTypeHint($parameter);
            }
            $phpdocType = $typeResolver->resolve($type);
            $phpdocType = $this->resolveSelf($phpdocType, $parameter->getDeclaringClass());

            /** @var DocBlock\Tags\Param[] $paramTags */
            $paramTags = $docBlock->getTagsByName('param');
            $docBlockType = null;
            foreach ($paramTags as $paramTag) {
                if ($paramTag->getVariableName() === $parameter->getName()) {
                    $docBlockType = $paramTag->getType();
                    break;
                }
            }

            try {
                $arr = [
                    'type' => $this->mapType($phpdocType, $docBlockType, $allowsNull || $parameter->isDefaultValueAvailable(), true),
                ];
            } catch (TypeMappingException $e) {
                throw TypeMappingException::wrapWithParamInfo($e, $parameter);
            } catch (CannotMapTypeException $e) {
                throw CannotMapTypeException::wrapWithParamInfo($e, $parameter);
            }

            if ($parameter->allowsNull()) {
                $arr['defaultValue'] = null;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $arr['defaultValue'] = $parameter->getDefaultValue();
            }

            $args[$parameter->getName()] = $arr;
        }

        return $args;
    }

    /**
     * @param Type $type
     * @param Type|null $docBlockType
     * @return GraphQLType
     */
    private function mapType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType): GraphQLType
    {
        $graphQlType = null;

        if ($type instanceof Array_ || $type instanceof Iterable_ || $type instanceof Mixed_) {
            $graphQlType = $this->mapDocBlockType($type, $docBlockType, $isNullable, $mapToInputType);
        } else {
            try {
                $graphQlType = $this->toGraphQlType($type, $mapToInputType);
                if (!$isNullable) {
                    $graphQlType = GraphQLType::nonNull($graphQlType);
                }
            } catch (TypeMappingException | CannotMapTypeException $e) {
                // Is the type iterable? If yes, let's analyze the docblock
                // TODO: it would be better not to go through an exception for this.
                if ($type instanceof Object_) {
                    $fqcn = (string) $type->getFqsen();
                    $refClass = new ReflectionClass($fqcn);
                    // Note : $refClass->isIterable() is only accessible in PHP 7.2
                    if ($refClass->implementsInterface(Iterator::class) || $refClass->implementsInterface(IteratorAggregate::class)) {
                        $graphQlType = $this->mapDocBlockType($type, $docBlockType, $isNullable, $mapToInputType);
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

    private function mapDocBlockType(Type $type, ?Type $docBlockType, bool $isNullable, bool $mapToInputType): GraphQLType
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
                $unionTypes[] = $this->toGraphQlType($this->dropNullableType($singleDocBlockType), $mapToInputType);
            } catch (TypeMappingException | CannotMapTypeException $e) {
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
            $graphQlType = new UnionType($unionTypes, $this->typeMapper);
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
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @param Type $type
     * @param bool $mapToInputType
     * @return (InputType&GraphQLType)|(OutputType&GraphQLType)
     */
    private function toGraphQlType(Type $type, bool $mapToInputType): GraphQLType
    {
        if ($type instanceof Integer) {
            return GraphQLType::int();
        } elseif ($type instanceof String_) {
            return GraphQLType::string();
        } elseif ($type instanceof Boolean) {
            return GraphQLType::boolean();
        } elseif ($type instanceof Float_) {
            return GraphQLType::float();
        } elseif ($type instanceof Object_) {
            $fqcn = (string) $type->getFqsen();
            if ($fqcn === '\\DateTimeImmutable' || $fqcn === '\\DateTimeInterface') {
                return DateTimeType::getInstance();
            } elseif ($fqcn === '\\DateTime') {
                throw new GraphQLException('Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
            }

            $className = ltrim($type->getFqsen(), '\\');
            if ($mapToInputType) {
                return $this->typeMapper->mapClassToInputType($className);
            } else {
                return $this->typeMapper->mapClassToInterfaceOrType($className);
            }
        } elseif ($type instanceof Array_) {
            return GraphQLType::listOf(GraphQLType::nonNull($this->toGraphQlType($type->getValueType(), $mapToInputType)));
        } else {
            throw TypeMappingException::createFromType($type);
        }
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
