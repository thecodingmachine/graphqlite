<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use function array_keys;
use function filemtime;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQL\Controllers\AnnotationReader;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and uses the @Type annotation to find the types automatically.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
final class GlobTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var int|null
     */
    private $globTtl;
    /**
     * @var array<string,string> Maps a domain class to the GraphQL type annotated class
     */
    private $mapClassToTypeArray = [];
    /**
     * @var array<string,string> Maps a GraphQL type name to the GraphQL type annotated class
     */
    private $mapNameToType = [];
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;
    /**
     * @var int|null
     */
    private $mapTtl;
    /**
     * @var bool
     */
    private $fullMapComputed = false;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(string $namespace, TypeGenerator $typeGenerator, ContainerInterface $container, AnnotationReader $annotationReader, CacheInterface $cache, ?int $globTtl = 2, ?int $mapTtl = null)
    {
        $this->namespace = $namespace;
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->cache = $cache;
        $this->globTtl = $globTtl;
        $this->typeGenerator = $typeGenerator;
        $this->mapTtl = $mapTtl;
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return array<string,string>
     */
    private function getMap(): array
    {
        if ($this->fullMapComputed === false) {
            $key = 'globTypeMapper_'.str_replace('\\', '_', $this->namespace);
            $this->mapClassToTypeArray = $this->cache->get($key);
            if ($this->mapClassToTypeArray === null) {
                $this->buildMap();
                // This is a very short lived cache. Useful to avoid overloading a server in case of heavy load.
                // Defaults to 2 seconds.
                $this->cache->set($key, $this->mapClassToTypeArray, $this->globTtl);
            }
        }
        return $this->mapClassToTypeArray;
    }

    private function buildMap(): void
    {
        $explorer = new GlobClassExplorer($this->namespace, $this->cache, $this->globTtl, ClassNameMapper::createFromComposerFile(null, null, true));
        $classes = $explorer->getClasses();
        foreach ($classes as $className) {
            if (!\class_exists($className)) {
                continue;
            }
            $refClass = new \ReflectionClass($className);
            if (!$refClass->isInstantiable()) {
                continue;
            }

            $type = $this->annotationReader->getTypeAnnotation($refClass);

            if ($type === null) {
                continue;
            }
            if (isset($this->mapClassToTypeArray[$type->getClass()])) {
                if ($this->mapClassToTypeArray[$type->getClass()] === $className) {
                    // Already mapped. Let's continue
                    continue;
                }
                throw DuplicateMappingException::create($type->getClass(), $this->mapClassToTypeArray[$type->getClass()], $className);
            }
            $this->storeTypeInCache($className, $type, $refClass->getFileName());
        }
        $this->fullMapComputed = true;
    }

    /**
     * Stores in cache the mapping TypeClass <=> Object class <=> GraphQL type name.
     */
    private function storeTypeInCache(string $typeClassName, Type $type, string $typeFileName)
    {
        $objectClassName = $type->getClass();
        $this->mapClassToTypeArray[$objectClassName] = $typeClassName;
        $this->cache->set('globTypeMapperByClass_'.str_replace('\\', '_', $objectClassName), [
            'filemtime' => filemtime($typeFileName),
            'typeFileName' => $typeFileName,
            'typeClass' => $typeClassName
        ], $this->mapTtl);
        $typeName = $this->typeGenerator->getName($typeClassName, $type);
        $this->mapNameToType[$typeName] = $typeClassName;
        $this->cache->set('globTypeMapperByName_'.$typeName, [
            'filemtime' => filemtime($typeFileName),
            'typeFileName' => $typeFileName,
            'typeClass' => $typeClassName
        ], $this->mapTtl);
    }

    private function getTypeFromCacheByObjectClass(string $className): ?string
    {
        if (isset($this->mapClassToTypeArray[$className])) {
            return $this->mapClassToTypeArray[$className];
        }

        // Let's try from the cache
        $item = $this->cache->get('globTypeMapperByClass_'.str_replace('\\', '_', $className));
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'typeFileName' => $typeFileName,
                'typeClass' => $typeClassName
            ] = $item;

            if ($filemtime === filemtime($typeFileName)) {
                $this->mapClassToTypeArray[$className] = $typeClassName;
                return $typeClassName;
            }
        }

        // cache miss
        return null;
    }

    private function getTypeFromCacheByGraphQLTypeName(string $graphqlTypeName): ?string
    {
        if (isset($this->mapNameToType[$graphqlTypeName])) {
            return $this->mapNameToType[$graphqlTypeName];
        }

        // Let's try from the cache
        $item = $this->cache->get('globTypeMapperByName_'.$graphqlTypeName);
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'typeFileName' => $typeFileName,
                'typeClass' => $typeClassName
            ] = $item;

            if ($filemtime === filemtime($typeFileName)) {
                $this->mapNameToType[$graphqlTypeName] = $typeClassName;
                return $typeClassName;
            }
        }

        // cache miss
        return null;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        $typeClassName = $this->getTypeFromCacheByObjectClass($className);

        if ($typeClassName === null) {
            $this->buildMap();
        }

        return isset($this->mapClassToTypeArray[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return ObjectType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): ObjectType
    {
        $typeClassName = $this->getTypeFromCacheByObjectClass($className);

        if ($typeClassName === null) {
            $this->buildMap();
        }

        if (!isset($this->mapClassToTypeArray[$className])) {
            throw CannotMapTypeException::createForType($className);
        }
        return $this->typeGenerator->mapAnnotatedObject($this->container->get($this->mapClassToTypeArray[$className]), $recursiveTypeMapper);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        return array_keys($this->getMap());
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputType
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType
    {
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return \GraphQL\Type\Definition\Type&(InputType|OutputType)
     * @throws CannotMapTypeException
     * @throws \ReflectionException
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): \GraphQL\Type\Definition\Type
    {
        $typeClassName = $this->getTypeFromCacheByGraphQLTypeName($typeName);

        if ($typeClassName === null) {
            $this->buildMap();
        }

        if (!isset($this->mapNameToType[$typeName])) {
            throw CannotMapTypeException::createForName($typeName);
        }
        return $this->typeGenerator->mapAnnotatedObject($this->container->get($this->mapNameToType[$typeName]), $recursiveTypeMapper);
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     * @return bool
     */
    public function canMapNameToType(string $typeName): bool
    {
        $typeClassName = $this->getTypeFromCacheByGraphQLTypeName($typeName);

        if ($typeClassName !== null) {
            return true;
        }

        $this->buildMap();

        return isset($this->mapNameToType[$typeName]);
    }
}
