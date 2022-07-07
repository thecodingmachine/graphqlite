<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Webmozart\Assert\Assert;

use function filemtime;
use function get_class;
use function md5;

/**
 * Creates DocBlocks and puts these in cache.
 */
class CachedDocBlockFactory
{
    /** @var CacheInterface */
    private $cache;
    /** @var DocBlockFactory */
    private $docBlockFactory;
    /** @var array<string, DocBlock> */
    private $docBlockArrayCache = [];
    /** @var array<string, Context> */
    private $contextArrayCache = [];
    /** @var ContextFactory */
    private $contextFactory;

    /**
     * @param CacheInterface $cache The cache we fetch data from. Note this is a SAFE cache. It does not need to be purged.
     */
    public function __construct(CacheInterface $cache, ?DocBlockFactory $docBlockFactory = null)
    {
        $this->cache           = $cache;
        $this->docBlockFactory = $docBlockFactory ?: DocBlockFactory::createInstance();
        $this->contextFactory  = new ContextFactory();
    }

    /**
     * Fetches a DocBlock object from a ReflectionMethod
     *
     * @param ReflectionMethod|ReflectionProperty $reflector
     *
     * @throws InvalidArgumentException
     */
    public function getDocBlock($reflector): DocBlock
    {
        $key = 'docblock_' . md5($reflector->getDeclaringClass()->getName() . '::' . $reflector->getName() . '::' . get_class($reflector));
        if (isset($this->docBlockArrayCache[$key])) {
            return $this->docBlockArrayCache[$key];
        }

        $fileName = $reflector->getDeclaringClass()->getFileName();
        Assert::string($fileName);

        $cacheItem = $this->cache->get($key);
        if ($cacheItem !== null) {
            [
                'time' => $time,
                'docblock' => $docBlock,
            ] = $cacheItem;

            if (filemtime($fileName) === $time) {
                $this->docBlockArrayCache[$key] = $docBlock;

                return $docBlock;
            }
        }

        $docBlock = $this->doGetDocBlock($reflector);

        $this->cache->set($key, [
            'time' => filemtime($fileName),
            'docblock' => $docBlock,
        ]);
        $this->docBlockArrayCache[$key] = $docBlock;

        return $docBlock;
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflector
     */
    private function doGetDocBlock($reflector): DocBlock
    {
        $docComment = $reflector->getDocComment() ?: '/** */';

        $refClass     = $reflector->getDeclaringClass();
        $refClassName = $refClass->getName();

        if (! isset($this->contextArrayCache[$refClassName])) {
            $this->contextArrayCache[$refClassName] = $this->contextFactory->createFromReflector($reflector);
        }

        return $this->docBlockFactory->create($docComment, $this->contextArrayCache[$refClassName]);
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public function getContextFromClass(ReflectionClass $reflectionClass): Context
    {
        $className = $reflectionClass->getName();
        if (isset($this->contextArrayCache[$className])) {
            return $this->contextArrayCache[$className];
        }

        $key = 'docblockcontext_' . md5($className);

        $fileName = $reflectionClass->getFileName();
        Assert::string($fileName);

        $cacheItem = $this->cache->get($key);
        if ($cacheItem !== null) {
            [
                'time' => $time,
                'context' => $context,
            ] = $cacheItem;

            if (filemtime($fileName) === $time) {
                $this->contextArrayCache[$className] = $context;

                return $context;
            }
        }

        $context = $this->contextFactory->createFromReflector($reflectionClass);

        $this->cache->set($key, [
            'time' => filemtime($fileName),
            'context' => $context,
        ]);

        $this->contextArrayCache[$className] = $context;
        return $context;
    }
}
