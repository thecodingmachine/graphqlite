<?php

namespace TheCodingMachine\GraphQLite\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Cache\Simple\ArrayCache;

class CachedDocBlockFactoryTest extends TestCase
{

    public function testGetDocBlock()
    {
        $arrayCache = new ArrayCache();
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
}
