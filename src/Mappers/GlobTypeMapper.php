<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use Doctrine\Common\Annotations\Reader;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
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
     * @var Reader
     */
    private $annotationReader;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var int|null
     */
    private $cacheTtl;
    /**
     * @var array<string,string>|null
     */
    private $map;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(string $namespace, TypeGenerator $typeGenerator, ContainerInterface $container, Reader $annotationReader, CacheInterface $cache, ?int $cacheTtl = null)
    {
        $this->namespace = $namespace;
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
        $this->typeGenerator = $typeGenerator;
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return array<string,string>
     */
    private function getMap(): array
    {
        if ($this->map === null) {
            $key = 'globTypeMapper_'.str_replace('\\', '_', $this->namespace);
            $this->map = $this->cache->get($key);
            if ($this->map === null) {
                $this->map = $this->buildMap();
                $this->cache->set($key, $this->map, $this->cacheTtl);
            }
        }
        return $this->map;
    }

    /**
     * @return array<string,string>
     */
    private function buildMap(): array
    {
        $explorer = new GlobClassExplorer($this->namespace, $this->cache, $this->cacheTtl, ClassNameMapper::createFromComposerFile(null, null, true));
        $classes = $explorer->getClasses();
        $map = [];
        foreach ($classes as $className) {
            if (!\class_exists($className)) {
                continue;
            }
            $refClass = new \ReflectionClass($className);
            /** @var Type|null $type */
            $type = $this->annotationReader->getClassAnnotation($refClass, Type::class);
            if ($type === null) {
                continue;
            }
            if (isset($map[$type->getClass()])) {
                throw DuplicateMappingException::create($type->getClass(), $map[$type->getClass()], $className);
            }
            $map[$type->getClass()] = $className;
        }
        return $map;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        $map = $this->getMap();
        return isset($map[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className
     * @return OutputType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType
    {
        $map = $this->getMap();
        if (!isset($map[$className])) {
            throw CannotMapTypeException::createForType($className);
        }
        return $this->typeGenerator->mapAnnotatedObject($this->container->get($map[$className]));
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
}
