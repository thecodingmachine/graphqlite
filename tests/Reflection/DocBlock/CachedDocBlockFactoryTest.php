<?php

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Cache\SnapshotClassBoundCache;

#[CoversClass(CachedDocBlockFactory::class)]
class CachedDocBlockFactoryTest extends TestCase
{

    public function testCreatesDocBlock(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter(storeSerialized: false));
        $cachedDocBlockFactory = new CachedDocBlockFactory(
            new SnapshotClassBoundCache($arrayCache, FilesSnapshot::alwaysUnchanged(...)),
            PhpDocumentorDocBlockFactory::default(),
        );

        $refMethod = new ReflectionMethod(DocBlockFactory::class, 'create');

        $docBlock = $cachedDocBlockFactory->create($refMethod);
        $this->assertSame('Fetches a DocBlock object from a ReflectionMethod', $docBlock->getSummary());
        $docBlock2 = $cachedDocBlockFactory->create($refMethod);
        $this->assertSame($docBlock2, $docBlock);

        $newCachedDocBlockFactory = new CachedDocBlockFactory(
            new SnapshotClassBoundCache($arrayCache, FilesSnapshot::alwaysUnchanged(...)),
            PhpDocumentorDocBlockFactory::default(),
        );
        $docBlock3 = $newCachedDocBlockFactory->create($refMethod);
        $this->assertEquals($docBlock3, $docBlock);
    }

    public function testCreatesContext(): void
    {
        $arrayCache = new Psr16Cache(new ArrayAdapter(storeSerialized: false));
        $cachedDocBlockFactory = new CachedDocBlockFactory(
            new SnapshotClassBoundCache($arrayCache, FilesSnapshot::alwaysUnchanged(...)),
            PhpDocumentorDocBlockFactory::default(),
        );

        $refMethod = new ReflectionMethod(DocBlockFactory::class, 'create');

        $docBlock = $cachedDocBlockFactory->createContext($refMethod);
        $this->assertSame('TheCodingMachine\GraphQLite\Reflection\DocBlock', $docBlock->getNamespace());
        $docBlock2 = $cachedDocBlockFactory->createContext($refMethod);
        $this->assertSame($docBlock2, $docBlock);

        $newCachedDocBlockFactory = new CachedDocBlockFactory(
            new SnapshotClassBoundCache($arrayCache, FilesSnapshot::alwaysUnchanged(...)),
            PhpDocumentorDocBlockFactory::default(),
        );
        $docBlock3 = $newCachedDocBlockFactory->createContext($refMethod);
        $this->assertEquals($docBlock3, $docBlock);
    }
}
