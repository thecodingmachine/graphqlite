<?php

namespace TheCodingMachine\GraphQLite\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;

class CachedDocBlockFactoryTest extends TestCase
{

    public function testGetDocBlock(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $cachedDocBlockFactory = new CachedDocBlockFactory($arrayCache);

        $refMethod = new ReflectionMethod(CachedDocBlockFactory::class, 'getDocBlock');

        $docBlock = $cachedDocBlockFactory->getDocBlock($refMethod);
        $this->assertSame('Fetches a DocBlock object from a ReflectionMethod', $docBlock->getSummary());
        $docBlock2 = $cachedDocBlockFactory->getDocBlock($refMethod);
        $this->assertSame($docBlock2, $docBlock);

        $newCachedDocBlockFactory = new CachedDocBlockFactory($arrayCache);
        $docBlock3 = $newCachedDocBlockFactory->getDocBlock($refMethod);
        $this->assertEquals($docBlock3, $docBlock);
    }

    public function testGetContext(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $cachedDocBlockFactory = new CachedDocBlockFactory($arrayCache);

        $refClass = new ReflectionClass(CachedDocBlockFactory::class);

        $context = $cachedDocBlockFactory->getContextFromClass($refClass);

        $context2 = $cachedDocBlockFactory->getContextFromClass($refClass);
        $this->assertSame($context2, $context);

        $newCachedDocBlockFactory = new CachedDocBlockFactory($arrayCache);
        $context3 = $newCachedDocBlockFactory->getContextFromClass($refClass);
        $this->assertEquals($context3, $context);
    }
}
