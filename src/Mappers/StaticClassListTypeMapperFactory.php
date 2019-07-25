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
use TheCodingMachine\GraphQLite\TypeRegistry;
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
final class StaticClassListTypeMapperFactory implements TypeMapperFactoryInterface
{
    /**
     * @var array<int, string> The list of classes to be scanned.
     */
    private $classList;
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var CacheInterface */
    protected $cache;
    /** @var int|null */
    protected $globTtl;

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
    /** @var GlobTypeMapperCache */
    private $globTypeMapperCache;
    /** @var GlobExtendTypeMapperCache */
    private $globExtendTypeMapperCache;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(array $classList,
                                TypeGenerator $typeGenerator,
                                InputTypeGenerator $inputTypeGenerator,
                                InputTypeUtils $inputTypeUtils,
                                ContainerInterface $container,
                                AnnotationReader $annotationReader,
                                NamingStrategyInterface $namingStrategy,
                                CacheInterface $cache,
                                ?int $globTtl = 2,
                                ?int $mapTtl = null)
    {
        $this->classList           = $classList;
        $this->typeGenerator       = $typeGenerator;
        $this->container           = $container;
        $this->annotationReader    = $annotationReader;
        $this->namingStrategy      = $namingStrategy;
        $this->cache               = $cache;
        $this->globTtl             = $globTtl;
        $this->mapTtl              = $mapTtl;
        $this->inputTypeGenerator  = $inputTypeGenerator;
        $this->inputTypeUtils      = $inputTypeUtils;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    public function create(RecursiveTypeMapperInterface $recursiveTypeMapper, TypeRegistry $typeRegistry): TypeMapperInterface
    {
        $typeGenerator      = new TypeGenerator($this->annotationReader, $this->namingStrategy, $typeRegistry, $this->container, $recursiveTypeMapper, $this->fieldsBuilder);

        return new StaticClassListTypeMapper(
            $this->classList,
            $this->typeGenerator,
            $this->inputTypeGenerator,
            $this->inputTypeUtils,
            $this->container,
            $this->annotationReader,
            $this->namingStrategy,
            $recursiveTypeMapper,
            $this->cache,
            $this->globTtl,
            $this->mapTtl
        );
    }
}
