<?php


namespace TheCodingMachine\GraphQLite\Mappers;


use function array_flip;
use function array_reverse;
use function get_parent_class;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\InterfaceFromObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedObjectType;

/**
 * This class wraps a TypeMapperInterface into a RecursiveTypeMapperInterface.
 * While the wrapped class does only tests one given class, the recursive type mapper
 * tests the class and all its parents.
 */
class RecursiveTypeMapper implements RecursiveTypeMapperInterface
{
    /**
     * @var TypeMapperInterface
     */
    private $typeMapper;

    /**
     * An array mapping a class name to the MappedClass instance (useful to know if the class has children)
     *
     * @var array<string,MappedClass>|null
     */
    private $mappedClasses;

    /**
     * An array of interfaces OR object types if no interface matching.
     *
     * @var array<string,OutputType&Type>
     */
    private $interfaces = [];

    /**
     * @var array<string,MutableObjectType> Key: FQCN
     */
    private $classToTypeCache = [];

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var int|null
     */
    private $ttl;

    /**
     * @var array<string, string> An array mapping a GraphQL interface name to the PHP class name that triggered its generation.
     */
    private $interfaceToClassNameMap;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;


    public function __construct(TypeMapperInterface $typeMapper, NamingStrategyInterface $namingStrategy, CacheInterface $cache, TypeRegistry $typeRegistry, ?int $ttl = null)
    {
        $this->typeMapper = $typeMapper;
        $this->namingStrategy = $namingStrategy;
        $this->cache = $cache;
        $this->ttl = $ttl;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        return $this->findClosestMatchingParent($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type)
     * @param (OutputType&MutableObjectType)|(OutputType&InterfaceType)|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     * @return MutableObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableObjectType
    {
        $cacheKey = $className;
        if ($subType !== null) {
            $cacheKey .= '__`__'.$subType->name;
        }
        if (isset($this->classToTypeCache[$cacheKey])) {
            return $this->classToTypeCache[$cacheKey];
        }

        $closestClassName = $this->findClosestMatchingParent($className);
        if ($closestClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }
        $type = $this->typeMapper->mapClassToType($closestClassName, $subType, $this);

        // In the event this type was already part of cache, let's not extend it.
        if ($this->typeRegistry->hasType($type->name)) {
            $cachedType = $this->typeRegistry->getType($type->name);
            if ($cachedType !== $type) {
                throw new \RuntimeException('Cached type in registry is not the type returned by type mapper.');
            }
            //if ($cachedType->getStatus() === MutableObjectType::STATUS_FROZEN) {
                return $type;
            //}
        }

        $this->typeRegistry->registerType($type);
        $this->classToTypeCache[$cacheKey] = $type;

        $this->extendType($className, $type);

        $type->freeze();

        return $type;
    }

    /**
     * Returns the closest parent that can be mapped, or null if nothing can be matched.
     *
     * @param string $className
     * @return string|null
     */
    public function findClosestMatchingParent(string $className): ?string
    {
        do {
            if ($this->typeMapper->canMapClassToType($className)) {
                return $className;
            }
        } while ($className = get_parent_class($className));
        return null;
    }

    /**
     * Extends a type using available type extenders.
     *
     * @param string $className
     * @param MutableObjectType $type
     * @throws CannotMapTypeExceptionInterface
     */
    private function extendType(string $className, MutableObjectType $type): void
    {
        $classes = [];
        do {
            if ($this->typeMapper->canExtendTypeForClass($className, $type, $this)) {
                $classes[] = $className;
            }
        } while ($className = get_parent_class($className));

        // Let's apply extenders from the most basic type.
        $classes = array_reverse($classes);
        foreach ($classes as $class) {
            $this->typeMapper->extendTypeForClass($class, $type, $this);
        }
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type. Returns an interface if possible (if the class
     * has children) or returns an output type otherwise.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @param (OutputType&ObjectType)|(OutputType&InterfaceType)|null $subType A subtype (if the main className is an iterator)
     * @return OutputType&Type
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInterfaceOrType(string $className, ?OutputType $subType): OutputType
    {
        $closestClassName = $this->findClosestMatchingParent($className);
        if ($closestClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }
        $cacheKey = $closestClassName;
        if ($subType !== null) {
            $cacheKey .= '__`__'.$subType->name;
        }
        if (!isset($this->interfaces[$cacheKey])) {
            $objectType = $this->mapClassToType($className, $subType);

            $supportedClasses = $this->getClassTree();
            if (isset($supportedClasses[$closestClassName]) && !empty($supportedClasses[$closestClassName]->getChildren())) {
                // Cast as an interface
                $this->interfaces[$cacheKey] = new InterfaceFromObjectType($this->namingStrategy->getInterfaceNameFromConcreteName($objectType->name), $objectType, $subType, $this);
                $this->typeRegistry->registerType($this->interfaces[$cacheKey]);
            } else {
                $this->interfaces[$cacheKey] = $objectType;
            }
        }
        return $this->interfaces[$cacheKey];
    }

    /**
     * Build a map mapping GraphQL interface names to the PHP class name of the object creating this interface.
     *
     * @return array<string, string>
     */
    private function buildInterfaceToClassNameMap(): array
    {
        $map = [];
        $supportedClasses = $this->getClassTree();
        foreach ($supportedClasses as $className => $mappedClass) {
            if (!empty($mappedClass->getChildren())) {
                $objectType = $this->mapClassToType($className, null);
                $interfaceName = $this->namingStrategy->getInterfaceNameFromConcreteName($objectType->name);
                $map[$interfaceName] = $className;
            }
        }
        return $map;
    }

    /**
     * Returns a map mapping GraphQL interface names to the PHP class name of the object creating this interface.
     * The map may come from the cache.
     *
     * @return array<string, string>
     */
    private function getInterfaceToClassNameMap(): array
    {
        if ($this->interfaceToClassNameMap === null) {
            $key = 'recursiveTypeMapper_interfaceToClassNameMap';
            $this->interfaceToClassNameMap = $this->cache->get($key);
            if ($this->interfaceToClassNameMap === null) {
                $this->interfaceToClassNameMap = $this->buildInterfaceToClassNameMap();
                // This is a very short lived cache. Useful to avoid overloading a server in case of heavy load.
                // Defaults to 2 seconds.
                $this->cache->set($key, $this->interfaceToClassNameMap, $this->ttl);
            }
        }
        return $this->interfaceToClassNameMap;
    }

    /**
     * Finds the list of interfaces returned by $className.
     *
     * @param string $className
     * @return InterfaceType[]
     */
    public function findInterfaces(string $className): array
    {
        $interfaces = [];
        while ($className = $this->findClosestMatchingParent($className)) {
            $type = $this->mapClassToInterfaceOrType($className, null);
            if ($type instanceof InterfaceType) {
                $interfaces[] = $type;
            }
            $className = get_parent_class($className);
            if ($className === false) {
                break;
            }
        }
        return $interfaces;
    }

    /**
     * @return array<string,MappedClass>
     */
    private function getClassTree(): array
    {
        if ($this->mappedClasses === null) {
            $supportedClasses = array_flip($this->typeMapper->getSupportedClasses());
            foreach ($supportedClasses as $supportedClass => $foo) {
                $this->getMappedClass($supportedClass, $supportedClasses);
            }
        }
        return $this->mappedClasses;
    }

    /**
     * @param string $className
     * @param array<string,int> $supportedClasses
     * @return MappedClass
     */
    private function getMappedClass(string $className, array $supportedClasses): MappedClass
    {
        if (!isset($this->mappedClasses[$className])) {
            $mappedClass = new MappedClass(/*$className*/);
            $this->mappedClasses[$className] = $mappedClass;
            $parentClassName = $className;
            while ($parentClassName = get_parent_class($parentClassName)) {
                if (isset($supportedClasses[$parentClassName])) {
                    $parentMappedClass = $this->getMappedClass($parentClassName, $supportedClasses);
                    //$mappedClass->setParent($parentMappedClass);
                    $parentMappedClass->addChild($mappedClass);
                    break;
                }
            }
        }
        return $this->mappedClasses[$className];
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        return $this->typeMapper->canMapClassToInputType($className);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className): InputObjectType
    {
        return $this->typeMapper->mapClassToInputType($className, $this);
    }

    /**
     * Returns an array containing all OutputTypes.
     * Needed for introspection because of interfaces.
     *
     * @return array<string, OutputType>
     */
    public function getOutputTypes(): array
    {
        $types = [];
        $typeNames = [];
        foreach ($this->typeMapper->getSupportedClasses() as $supportedClass) {
            $type = $this->mapClassToType($supportedClass, null);
            $types[$supportedClass] = $type;
            if (isset($typeNames[$type->name])) {
                throw DuplicateMappingException::createForTypeName($type->name, $typeNames[$type->name], $supportedClass);
            }
            $typeNames[$type->name] = $supportedClass;
        }
        return $types;
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     * @return bool
     */
    public function canMapNameToType(string $typeName): bool
    {
        $result = $this->typeMapper->canMapNameToType($typeName);
        if ($result === true) {
            return true;
        }

        // Maybe the type is an interface?
        $interfaceToClassNameMap = $this->getInterfaceToClassNameMap();
        if (isset($interfaceToClassNameMap[$typeName])) {
            return true;
        }

        return false;
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     * @return Type&(InputType|OutputType)
     */
    public function mapNameToType(string $typeName): Type
    {
        if ($this->typeRegistry->hasType($typeName)) {
            return $this->typeRegistry->getType($typeName);
        }
        if ($this->typeMapper->canMapNameToType($typeName)) {
            $type = $this->typeMapper->mapNameToType($typeName, $this);

            if ($this->typeRegistry->hasType($typeName)) {
                $cachedType = $this->typeRegistry->getType($typeName);
                if ($cachedType !== $type) {
                    throw new \RuntimeException('Cached type in registry is not the type returned by type mapper.');
                }
                if ($cachedType instanceof MutableObjectType && $cachedType->getStatus() === MutableObjectType::STATUS_FROZEN) {
                    return $type;
                }
            }

            if (!$this->typeRegistry->hasType($typeName)) {
                $this->typeRegistry->registerType($type);
            }
            if ($type instanceof MutableObjectType) {
                if ($this->typeMapper->canExtendTypeForName($typeName, $type, $this)) {
                    $this->typeMapper->extendTypeForName($typeName, $type, $this);
                }
                $type->freeze();
            }
            return $type;
        }

        // Maybe the type is an interface?
        $interfaceToClassNameMap = $this->getInterfaceToClassNameMap();
        if (isset($interfaceToClassNameMap[$typeName])) {
            $className = $interfaceToClassNameMap[$typeName];
            return $this->mapClassToInterfaceOrType($className, null);
        }

        throw CannotMapTypeException::createForName($typeName);
    }
}
