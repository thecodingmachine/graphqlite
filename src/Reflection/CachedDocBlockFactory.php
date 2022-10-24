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

use function assert;
use function filemtime;
use function is_string;
use function md5;

/**
 * Creates DocBlocks and puts these in cache.
 */
class CachedDocBlockFactory
{
    private DocBlockFactory $docBlockFactory;
    /** @var array<string, DocBlock> */
    private array $docBlockArrayCache = [];
    /** @var array<string, Context> */
    private array $contextArrayCache = [];
    private ContextFactory $contextFactory;

    /** @param CacheInterface $cache The cache we fetch data from. Note this is a SAFE cache. It does not need to be purged. */
    public function __construct(private CacheInterface $cache, DocBlockFactory|null $docBlockFactory = null)
    {
        $this->docBlockFactory = $docBlockFactory ?: DocBlockFactory::createInstance();
        $this->contextFactory  = new ContextFactory();
    }

    /**
     * Fetches a DocBlock object from a ReflectionMethod
     *
     * @throws InvalidArgumentException
     */
    public function getDocBlock(ReflectionMethod|ReflectionProperty $reflector): DocBlock
    {
        $key = 'docblock_' . md5($reflector->getDeclaringClass()->getName() . '::' . $reflector->getName() . '::' . $reflector::class);
        if (isset($this->docBlockArrayCache[$key])) {
            return $this->docBlockArrayCache[$key];
        }

        $fileName = $reflector->getDeclaringClass()->getFileName();
        assert(is_string($fileName));

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

    private function doGetDocBlock(ReflectionMethod|ReflectionProperty $reflector): DocBlock
    {
        $docComment = $reflector->getDocComment() ?: '/** */';

        $refClass     = $reflector->getDeclaringClass();
        $refClassName = $refClass->getName();

        if (! isset($this->contextArrayCache[$refClassName])) {
            $this->contextArrayCache[$refClassName] = $this->contextFactory->createFromReflector($reflector);
        }

        return $this->docBlockFactory->create($docComment, $this->contextArrayCache[$refClassName]);
    }

    /** @param ReflectionClass<object> $reflectionClass */
    public function getContextFromClass(ReflectionClass $reflectionClass): Context
    {
        $className = $reflectionClass->getName();
        if (isset($this->contextArrayCache[$className])) {
            return $this->contextArrayCache[$className];
        }

        $key = 'docblockcontext_' . md5($className);

        $fileName = $reflectionClass->getFileName();
        assert(is_string($fileName));

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
