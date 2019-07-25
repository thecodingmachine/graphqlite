<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use function implode;
use Mouf\Composer\ClassNameMapper;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Contracts\Cache\CacheInterface as CacheContractInterface;
use TheCodingMachine\CacheUtils\ClassBoundCache;
use TheCodingMachine\CacheUtils\ClassBoundCacheContract;
use TheCodingMachine\CacheUtils\ClassBoundCacheContractInterface;
use TheCodingMachine\CacheUtils\ClassBoundMemoryAdapter;
use TheCodingMachine\CacheUtils\FileBoundCache;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use Webmozart\Assert\Assert;
use function class_exists;
use function interface_exists;
use function str_replace;

/**
 * A type mapper that is passed the list of classes that it must scan (unlike the GlobTypeMapper that find those automatically).
 */
final class StaticClassListTypeMapper extends AbstractTypeMapper
{
    /**
     * @var array<int, string> The list of classes to be scanned.
     */
    private $classList;
    /**
     * The array of classes.
     * Key: fully qualified class name
     *
     * @var array<string,ReflectionClass>
     */
    private $classes;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(array $classList, TypeGenerator $typeGenerator, InputTypeGenerator $inputTypeGenerator, InputTypeUtils $inputTypeUtils, ContainerInterface $container, AnnotationReader $annotationReader, NamingStrategyInterface $namingStrategy, RecursiveTypeMapperInterface $recursiveTypeMapper, CacheInterface $cache, ?int $globTtl = 2, ?int $mapTtl = null)
    {
        $this->classList = $classList;
        $cachePrefix = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', implode('_', $classList));
        parent::__construct($cachePrefix, $typeGenerator, $inputTypeGenerator, $inputTypeUtils, $container, $annotationReader, $namingStrategy, $recursiveTypeMapper, $cache, $globTtl, $mapTtl);
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass> Key: fully qualified class name
     */
    protected function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            foreach ($this->classList as $className) {
                if (! class_exists($className) && ! interface_exists($className)) {
                    throw new GraphQLException('Could not find class "'.$className.'"');
                }
                $refClass = new ReflectionClass($className);
                if (! $refClass->isInstantiable() && ! $refClass->isInterface()) {
                    throw new GraphQLException('Class "'.$className.'" must be instantiable or be an interface.');
                }
                $this->classes[$className] = $refClass;
            }
        }

        return $this->classes;
    }
}
