<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderBoundCache;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use function assert;

/**
 * Analyzes classes and uses the @Type annotation to find the types automatically.
 */
class ClassFinderTypeMapper implements TypeMapperInterface
{
    private GlobTypeMapperCache|null $globTypeMapperCache = null;
    private GlobExtendTypeMapperCache|null $globExtendTypeMapperCache = null;

    public function __construct(
        private readonly ClassFinder                  $classFinder,
        private readonly TypeGenerator                $typeGenerator,
        private readonly InputTypeGenerator           $inputTypeGenerator,
        private readonly InputTypeUtils               $inputTypeUtils,
        private readonly ContainerInterface           $container,
        private readonly AnnotationReader             $annotationReader,
        private readonly NamingStrategyInterface      $namingStrategy,
        private readonly RecursiveTypeMapperInterface $recursiveTypeMapper,
        private readonly ClassFinderBoundCache        $classFinderBoundCache,
    )
    {
    }

    /**
     * Returns an object mapping all types.
     */
    private function getMaps(): GlobTypeMapperCache
    {
        $this->globTypeMapperCache ??= $this->classFinderBoundCache->reduce(
            $this->classFinder,
            'classToAnnotations',
            function (ReflectionClass $refClass): ?GlobAnnotationsCache {
                if ($refClass->isEnum()) {
                    return null;
                }

                $annotationsCache = new GlobAnnotationsCache(
                    $className = $refClass->getName(),
                );

                $containsAnnotations = false;

                $type = $this->annotationReader->getTypeAnnotation($refClass);
                if ($type !== null) {
                    $typeName = $this->namingStrategy->getOutputTypeName($className, $type);
                    $annotationsCache = $annotationsCache->withType($type->getClass(), $typeName, $type->isDefault());
                    $containsAnnotations = true;
                }

                $inputs = $this->annotationReader->getInputAnnotations($refClass);
                foreach ($inputs as $input) {
                    $inputName = $this->namingStrategy->getInputTypeName($className, $input);
                    $annotationsCache = $annotationsCache->registerInput($inputName, $className, $input);
                    $containsAnnotations = true;
                }

                $isAbstract = $refClass->isAbstract();

                foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($isAbstract && ! $method->isStatic()) {
                        continue;
                    }
                    $factory = $this->annotationReader->getFactoryAnnotation($method);

                    if ($factory !== null) {
                        [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

                        $annotationsCache = $annotationsCache->registerFactory($method->getName(), $inputName, $className, $factory->isDefault(), $refClass->getName());
                        $containsAnnotations = true;
                    }

                    $decorator = $this->annotationReader->getDecorateAnnotation($method);

                    if ($decorator === null) {
                        continue;
                    }

                    $annotationsCache = $annotationsCache->registerDecorator($method->getName(), $decorator->getInputTypeName(), $refClass->getName());
                    $containsAnnotations = true;
                }

                return $containsAnnotations ? $annotationsCache : null;
            },
            fn (array $entries) => array_reduce($entries, function (GlobTypeMapperCache $globTypeMapperCache, ?GlobAnnotationsCache $annotationsCache) {
                if ($annotationsCache === null) {
                    return $globTypeMapperCache;
                }

                $globTypeMapperCache->registerAnnotations($annotationsCache->sourceClass, $annotationsCache);

                return $globTypeMapperCache;
            }, new GlobTypeMapperCache())
        );

        return $this->globTypeMapperCache;
    }

    private function getMapClassToExtendTypeArray(): GlobExtendTypeMapperCache
    {
        $this->globExtendTypeMapperCache ??= $this->classFinderBoundCache->reduce(
            $this->classFinder,
            'classToExtendAnnotations',
            function (ReflectionClass $refClass): ?GlobExtendAnnotationsCache {
                // Enum's are not types
                if ($refClass->isEnum()) {
                    return null;
                }

                $extendType = $this->annotationReader->getExtendTypeAnnotation($refClass);

                if ($extendType === null) {
                    return null;
                }

                $extendClassName = $extendType->getClass();
                if ($extendClassName !== null) {
                    try {
                        $targetType = $this->recursiveTypeMapper->mapClassToType($extendClassName, null);
                    } catch (CannotMapTypeException $e) {
                        $e->addExtendTypeInfo($refClass, $extendType);
                        throw $e;
                    }
                    $typeName = $targetType->name;
                } else {
                    $typeName = $extendType->getName();
                    assert($typeName !== null);
                    $targetType = $this->recursiveTypeMapper->mapNameToType($typeName);
                    if (! $targetType instanceof MutableObjectType) {
                        throw CannotMapTypeException::extendTypeWithBadTargetedClass($refClass->getName(), $extendType);
                    }
                    $extendClassName = $targetType->getMappedClassName();
                }

                // FIXME: $extendClassName === NULL!!!!!!
                return new GlobExtendAnnotationsCache($refClass->getName(), $extendClassName, $typeName);
            },
            fn (array $entries) => array_reduce($entries, function (GlobExtendTypeMapperCache $globExtendTypeMapperCache, ?GlobExtendAnnotationsCache $annotationsCache) {
                if ($annotationsCache === null) {
                    return $globExtendTypeMapperCache;
                }

                $globExtendTypeMapperCache->registerAnnotations($annotationsCache->sourceClass, $annotationsCache);

                return $globExtendTypeMapperCache;
            }, new GlobExtendTypeMapperCache())
        );

        return $this->globExtendTypeMapperCache;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param class-string<object> $className
     */
    public function canMapClassToType(string $className): bool
    {
        return $this->getMaps()->getTypeByObjectClass($className) !== null
            || $this->getMaps()->getInputByObjectClass($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param class-string<object> $className The exact class name to look for (this function does not look into parent classes).
     * @param OutputType|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, OutputType|null $subType): MutableInterface
    {
        /** @var class-string<object>|null $inputTypeClassName */
        $inputTypeClassName = $this->getMaps()->getInputByObjectClass($className)
            ? $this->getMaps()->getInputByObjectClass($className)[0]
            : null;

        $typeClassName = $this->getMaps()->getTypeByObjectClass($className) ?: $inputTypeClassName;
        if ($typeClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }

        return $this->typeGenerator->mapAnnotatedObject($typeClassName);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return array<int,string>
     */
    public function getSupportedClasses(): array
    {
        return $this->getMaps()->getSupportedClasses();
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     */
    public function canMapClassToInputType(string $className): bool
    {
        if ($this->getMaps()->getFactoryByObjectClass($className) !== null) {
            return true;
        }

        return $this->getMaps()->getInputByObjectClass($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param class-string<object> $className
     *
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface
    {
        $factory = $this->getMaps()->getFactoryByObjectClass($className);

        if ($factory !== null) {
            return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $this->container);
        }

        $input = $this->getMaps()->getInputByObjectClass($className);
        if ($input !== null) {
            [$className, $typeName, $description, $isUpdate] = $input;
            return $this->inputTypeGenerator->mapInput($className, $typeName, $description, $isUpdate);
        }

        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     *
     * @return NamedType&Type&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType|MutableInterfaceType)
     *
     * @throws CannotMapTypeExceptionInterface
     * @throws ReflectionException
     */
    public function mapNameToType(string $typeName): Type&NamedType
    {
        $typeClassName = $this->getMaps()->getTypeByGraphQLTypeName($typeName);

        if ($typeClassName !== null) {
            return $this->typeGenerator->mapAnnotatedObject($typeClassName);
        }

        $factory = $this->getMaps()->getFactoryByGraphQLInputTypeName($typeName);
        if ($factory !== null) {
            return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $this->container);
        }

        $input = $this->getMaps()->getInputByGraphQLInputTypeName($typeName);
        if ($input !== null) {
            [$className, $description, $isUpdate] = $input;
            return $this->inputTypeGenerator->mapInput($className, $typeName, $description, $isUpdate);
        }

        throw CannotMapTypeException::createForName($typeName);
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function canMapNameToType(string $typeName): bool
    {
        $typeClassName = $this->getMaps()->getTypeByGraphQLTypeName($typeName);

        if ($typeClassName !== null) {
            return true;
        }

        $factory = $this->getMaps()->getFactoryByGraphQLInputTypeName($typeName);

        if ($factory !== null) {
            return true;
        }

        return $this->getMaps()->getInputByGraphQLInputTypeName($typeName) !== null;
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForClass(string $className, MutableInterface $type): bool
    {
        return $this->getMapClassToExtendTypeArray()->getExtendTypesByObjectClass($className) !== null;
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param class-string<object> $className
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableInterface $type): void
    {
        $extendTypeClassNames = $this->getMapClassToExtendTypeArray()->getExtendTypesByObjectClass($className);

        if ($extendTypeClassNames === null) {
            throw CannotMapTypeException::createForExtendType($className, $type);
        }

        foreach ($extendTypeClassNames as $extendedTypeClass) {
            $this->typeGenerator->extendAnnotatedObject($this->container->get($extendedTypeClass), $type);
        }
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
    {
        $typeClassNames = $this->getMapClassToExtendTypeArray()->getExtendTypesByGraphQLTypeName($typeName);

        return $typeClassNames !== null;
    }

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableInterface $type): void
    {
        $extendTypeClassNames = $this->getMapClassToExtendTypeArray()->getExtendTypesByGraphQLTypeName($typeName);
        if ($extendTypeClassNames === null) {
            throw CannotMapTypeException::createForExtendName($typeName, $type);
        }

        foreach ($extendTypeClassNames as $extendedTypeClass) {
            $this->typeGenerator->extendAnnotatedObject($this->container->get($extendedTypeClass), $type);
        }
    }

    /**
     * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
     */
    public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
    {
        return ! empty($this->getMaps()->getDecorateByGraphQLInputTypeName($typeName));
    }

    /**
     * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
     *
     * @param ResolvableMutableInputInterface &InputObjectType $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
    {
        $decorators = $this->getMaps()->getDecorateByGraphQLInputTypeName($typeName);

        if (empty($decorators)) {
            throw CannotMapTypeException::createForDecorateName($typeName, $type);
        }

        foreach ($decorators as $decorator) {
            $this->inputTypeGenerator->decorateInputType($decorator[0], $decorator[1], $type, $this->container);
        }
    }
}
