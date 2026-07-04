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
        // Second arg false so the adapter stores by reference and returns the same instance for
        $arrayCache = new Psr16Cache(new ArrayAdapter(0, false));
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
        // Second arg false so the adapter stores by reference and returns the same instance for
        // assertSame. Passed positionally, not named: symfony/cache renamed it storeSerialized -> deepClone.
        $arrayCache = new Psr16Cache(new ArrayAdapter(0, false));
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
