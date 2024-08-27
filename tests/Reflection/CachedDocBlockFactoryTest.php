<?php

namespace TheCodingMachine\GraphQLite\Reflection;

use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\ContextFactory;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Cache\HardClassBoundCache;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockContextFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockFactory;

class CachedDocBlockFactoryTest extends TestCase
{

    public function testGetDocBlock(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter(storeSerialized: false));
        $cachedDocBlockFactory = new CachedDocBlockFactory(
            new HardClassBoundCache($arrayCache),
            new PhpDocumentorDocBlockFactory(
                DocBlockFactory::createInstance(),
                new PhpDocumentorDocBlockContextFactory(new ContextFactory()),
            )
        );

        $refMethod = new ReflectionMethod(DocBlock\DocBlockFactory::class, 'createFromReflector');

        $docBlock = $cachedDocBlockFactory->createFromReflector($refMethod);
        $this->assertSame('Fetches a DocBlock object from a ReflectionMethod', $docBlock->getSummary());
        $docBlock2 = $cachedDocBlockFactory->createFromReflector($refMethod);
        $this->assertSame($docBlock2, $docBlock);

        $newCachedDocBlockFactory = new CachedDocBlockFactory(
            new HardClassBoundCache($arrayCache),
            new PhpDocumentorDocBlockFactory(
                DocBlockFactory::createInstance(),
                new PhpDocumentorDocBlockContextFactory(new ContextFactory()),
            )
        );
        $docBlock3 = $newCachedDocBlockFactory->createFromReflector($refMethod);
        $this->assertEquals($docBlock3, $docBlock);
    }
}
