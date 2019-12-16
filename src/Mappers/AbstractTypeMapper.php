<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Contracts\Cache\CacheInterface as CacheContractInterface;
use TheCodingMachine\CacheUtils\ClassBoundCache;
use TheCodingMachine\CacheUtils\ClassBoundCacheContract;
use TheCodingMachine\CacheUtils\ClassBoundCacheContractInterface;
use TheCodingMachine\CacheUtils\ClassBoundMemoryAdapter;
use TheCodingMachine\CacheUtils\FileBoundCache;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use Webmozart\Assert\Assert;

/**
 * Analyzes classes and uses the @Type annotation to find the types automatically.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
abstract class AbstractTypeMapper implements TypeMapperInterface
{
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var CacheInterface */
    protected $cache;
    /** @var int|null */
    protected $globTtl;

    /**
     * Cache storing the GlobAnnotationsCache objects linked to a given ReflectionClass.
     *
     * @var ClassBoundCacheContractInterface
     */
    private $mapClassToAnnotationsCache;
    /**
     * Cache storing the GlobAnnotationsCache objects linked to a given ReflectionClass.
     *
     * @var ClassBoundCacheContractInterface
     */
    private $mapClassToExtendAnnotationsCache;

    /** @var ContainerInterface */
    private $container;
    /** @var TypeGenerator */
    private $typeGenerator;
    /** @var int|null */
    private $mapTtl;
    /** @var NamingStrategyInterface */
    private $namingStrategy;
    /** @var InputTypeGenerator */
    private $inputTypeGenerator;
    /** @var InputTypeUtils */
    private $inputTypeUtils;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var CacheContractInterface */
    private $cacheContract;
    /** @var GlobTypeMapperCache */
    private $globTypeMapperCache;
    /** @var GlobExtendTypeMapperCache */
    private $globExtendTypeMapperCache;

    public function __construct(string $cachePrefix, TypeGenerator $typeGenerator, InputTypeGenerator $inputTypeGenerator, InputTypeUtils $inputTypeUtils, ContainerInterface $container, AnnotationReader $annotationReader, NamingStrategyInterface $namingStrategy, RecursiveTypeMapperInterface $recursiveTypeMapper, CacheInterface $cache, ?int $globTtl = 2, ?int $mapTtl = null)
    {
        $this->typeGenerator       = $typeGenerator;
        $this->container           = $container;
        $this->annotationReader    = $annotationReader;
        $this->namingStrategy      = $namingStrategy;
        $this->cache               = $cache;
        $this->globTtl             = $globTtl;
        $this->cacheContract       = new Psr16Adapter($this->cache, $cachePrefix, $this->globTtl ?? 0);
        $this->mapTtl              = $mapTtl;
        $this->inputTypeGenerator  = $inputTypeGenerator;
        $this->inputTypeUtils      = $inputTypeUtils;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->mapClassToAnnotationsCache = new ClassBoundCacheContract(new ClassBoundMemoryAdapter(new ClassBoundCache(new FileBoundCache($this->cache, 'classToAnnotations_' . $cachePrefix))));
        $this->mapClassToExtendAnnotationsCache = new ClassBoundCacheContract(new ClassBoundMemoryAdapter(new ClassBoundCache(new FileBoundCache($this->cache, 'classToExtendAnnotations_' . $cachePrefix))));
    }

    /**
     * Returns an object mapping all types.
     */
    private function getMaps(): GlobTypeMapperCache
    {
        if ($this->globTypeMapperCache === null) {
            $this->globTypeMapperCache = $this->cacheContract->get('fullMapComputed', function () {
                return $this->buildMap();
            });
        }

        return $this->globTypeMapperCache;
    }

    private function getMapClassToExtendTypeArray(): GlobExtendTypeMapperCache
    {
        if ($this->globExtendTypeMapperCache === null) {
            $this->globExtendTypeMapperCache = $this->cacheContract->get('fullExtendMapComputed', function () {
                return $this->buildMapClassToExtendTypeArray();
            });
        }

        return $this->globExtendTypeMapperCache;
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass<object>> Key: fully qualified class name
     */
    abstract protected function getClassList(): array;

    private function buildMap(): GlobTypeMapperCache
    {
        $globTypeMapperCache = new GlobTypeMapperCache();

        $classes = $this->getClassList();
        foreach ($classes as $className => $refClass) {
            $annotationsCache = $this->mapClassToAnnotationsCache->get($refClass, function () use ($refClass, $className) {
                $annotationsCache = new GlobAnnotationsCache();

                $containsAnnotations = false;

                $type = $this->annotationReader->getTypeAnnotation($refClass);
                if ($type !== null) {
                    $typeName = $this->namingStrategy->getOutputTypeName($className, $type);
                    $annotationsCache->setType($type->getClass(), $typeName, $type->isDefault());
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

                        $annotationsCache->registerFactory($method->getName(), $inputName, $className, $factory->isDefault(), $refClass->getName());
                        $containsAnnotations = true;
                    }

                    $decorator = $this->annotationReader->getDecorateAnnotation($method);

                    if ($decorator === null) {
                        continue;
                    }

                    $annotationsCache->registerDecorator($method->getName(), $decorator->getInputTypeName(), $refClass->getName());
                    $containsAnnotations = true;
                }

                if (! $containsAnnotations) {
                    return 'nothing';
                }

                return $annotationsCache;
            }, '', $this->mapTtl);

            if ($annotationsCache === 'nothing') {
                continue;
            }

            $globTypeMapperCache->registerAnnotations($refClass, $annotationsCache);
        }

        return $globTypeMapperCache;
    }

    private function buildMapClassToExtendTypeArray(): GlobExtendTypeMapperCache
    {
        $globExtendTypeMapperCache = new GlobExtendTypeMapperCache();

        $classes = $this->getClassList();
        foreach ($classes as $className => $refClass) {
            $annotationsCache = $this->mapClassToExtendAnnotationsCache->get($refClass, function () use ($refClass) {
                $extendAnnotationsCache = new GlobExtendAnnotationsCache();

                $extendType = $this->annotationReader->getExtendTypeAnnotation($refClass);

                if ($extendType !== null) {
                    $extendClassName = $extendType->getClass();
                    if ($extendClassName !== null) {
                        try {
                            $targetType = $this->recursiveTypeMapper->mapClassToType($extendClassName, null);
                        } catch (CannotMapTypeException $e) {
                            $e->addExtendTypeInfo($refClass, $extendType);
                            throw $e;
                        }
                        $typeName   = $targetType->name;
                    } else {
                        $typeName = $extendType->getName();
                        Assert::notNull($typeName);
                        $targetType = $this->recursiveTypeMapper->mapNameToType($typeName);
                        if (! $targetType instanceof MutableObjectType) {
                            throw CannotMapTypeException::extendTypeWithBadTargetedClass($refClass->getName(), $extendType);
                        }
                        $extendClassName = $targetType->getMappedClassName();
                    }

                    // FIXME: $extendClassName === NULL!!!!!!
                    $extendAnnotationsCache->setExtendType($extendClassName, $typeName);

                    return $extendAnnotationsCache;
                }

                return 'nothing';
            }, '', $this->mapTtl);

            if ($annotationsCache === 'nothing') {
                continue;
            }

            $globExtendTypeMapperCache->registerAnnotations($refClass, $annotationsCache);
        }

        return $globExtendTypeMapperCache;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     */
    public function canMapClassToType(string $className): bool
    {
        return $this->getMaps()->getTypeByObjectClass($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @param OutputType|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableInterface
    {
        $typeClassName = $this->getMaps()->getTypeByObjectClass($className);

        if ($typeClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }

        return $this->typeGenerator->mapAnnotatedObject($typeClassName);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
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
        return $this->getMaps()->getFactoryByObjectClass($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @return ResolvableMutableInputInterface&InputObjectType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface
    {
        $factory = $this->getMaps()->getFactoryByObjectClass($className);

        if ($factory === null) {
            throw CannotMapTypeException::createForInputType($className);
        }

        return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $this->container);
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
    public function mapNameToType(string $typeName): Type
    {
        $typeClassName = $this->getMaps()->getTypeByGraphQLTypeName($typeName);

        if ($typeClassName !== null) {
            return $this->typeGenerator->mapAnnotatedObject($typeClassName);
        }

        $factory = $this->getMaps()->getFactoryByGraphQLInputTypeName($typeName);
        if ($factory !== null) {
            return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $this->container);
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

        return $factory !== null;
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
