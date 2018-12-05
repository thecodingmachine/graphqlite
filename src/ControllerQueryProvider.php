<?php


namespace TheCodingMachine\GraphQL\Controllers;

use function array_map;
use function array_merge;
use function get_class;
use function gettype;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use phpDocumentor\Reflection\Types\Nullable;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Types\UnionType;
use function is_object;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\ContextFactory;
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
 * A query provider that looks for queries in a "controller"
 */
class ControllerQueryProvider implements QueryProviderInterface
{
    /**
     * @var object
     */
    private $controller;
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
     * @param object $controller
     * @param AnnotationReader $annotationReader
     * @param RecursiveTypeMapperInterface $typeMapper
     * @param HydratorInterface $hydrator
     * @param AuthenticationServiceInterface $authenticationService
     * @param AuthorizationServiceInterface $authorizationService
     * @param ContainerInterface $registry The registry is used to fetch custom return types by container identifier (using the returnType parameter of the Type annotation)
     */
    public function __construct($controller, AnnotationReader $annotationReader, RecursiveTypeMapperInterface $typeMapper,
                                HydratorInterface $hydrator, AuthenticationServiceInterface $authenticationService,
                                AuthorizationServiceInterface $authorizationService, ContainerInterface $registry)
    {
        $this->controller = $controller;
        $this->annotationReader = $annotationReader;
        $this->typeMapper = $typeMapper;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->registry = $registry;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        return $this->getFieldsByAnnotations(Query::class, false);
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        return $this->getFieldsByAnnotations(Mutation::class, false);
    }

    /**
     * @return array<string, QueryField> QueryField indexed by name.
     */
    public function getFields(): array
    {
        $fieldAnnotations = $this->getFieldsByAnnotations(Annotations\Field::class, true);
        $sourceFields = $this->getSourceFields();

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
     * @param string $annotationName
     * @param bool $injectSource Whether to inject the source object or not as the first argument. True for @Field, false for @Query and @Mutation
     * @return QueryField[]
     * @throws \ReflectionException
     */
    private function getFieldsByAnnotations(string $annotationName, bool $injectSource): array
    {
        $refClass = new \ReflectionClass($this->controller);
        $docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $contextFactory = new ContextFactory();

        //$refClass = ReflectionClass::createFromInstance($this->controller);

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

                $docComment = $refMethod->getDocComment() ?: '/** */';

                // context is changing based on the class the method is declared in.
                // we assume methods will be returned packed by classes so we do this little cache
                if ($oldDeclaringClass !== $refMethod->getDeclaringClass()->getName()) {
                    $context = $contextFactory->createFromReflector($refMethod);
                    $oldDeclaringClass = $refMethod->getDeclaringClass()->getName();
                }

                $docBlockObj = $docBlockFactory->create($docComment, $context);

                // TODO: change CommentParser to use $docBlockObj methods instead.
                $docBlock = new CommentParser($refMethod->getDocComment());

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

                $queryList[] = new QueryField($name, $type, $args, [$this->controller, $methodName], null, $this->hydrator, $docBlock->getComment(), $injectSource);
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
     * @return QueryField[]
     */
    private function getSourceFields(): array
    {
        $refClass = new \ReflectionClass($this->controller);
        $docBlockFactory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $contextFactory = new ContextFactory();

        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->annotationReader->getSourceFields($refClass);

        if ($this->controller instanceof FromSourceFieldsInterface) {
            $sourceFields = array_merge($sourceFields, $this->controller->getSourceFields());
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

            $docBlock = new CommentParser($refMethod->getDocComment());

            $methodName = $refMethod->getName();


            // context is changing based on the class the method is declared in.
            // we assume methods will be returned packed by classes so we do this little cache
            if ($oldDeclaringClass !== $refMethod->getDeclaringClass()->getName()) {
                $context = $contextFactory->createFromReflector($refMethod);
                $oldDeclaringClass = $refMethod->getDeclaringClass()->getName();
            }

            $docComment = $refMethod->getDocComment() ?: '/** */';
            $docBlockObj = $docBlockFactory->create($docComment, $context);

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

            $queryList[] = new QueryField($sourceField->getName(), $type, $args, null, $methodName, $this->hydrator, $docBlock->getComment(), false);

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
     * @return array[] An array of ['type'=>Type, 'default'=>val]
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
                $arr['default'] = null;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $arr['default'] = $parameter->getDefaultValue();
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
        foreach ($filteredDocBlockTypes as $singleDocBlockType) {
            try {
                $unionTypes[] = $this->toGraphQlType($this->dropNullableType($singleDocBlockType), $mapToInputType);
            } catch (TypeMappingException | CannotMapTypeException $e) {
                // We have several types. It is ok not to be able to match one.
            }
        }

        if (empty($unionTypes)) {
            throw TypeMappingException::createFromType($docBlockType);
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
}
