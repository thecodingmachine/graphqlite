<?php

namespace TheCodingMachine\GraphQLite\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Cache\SnapshotClassBoundCache;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockFactory;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockFactory;

class CachedDocBlockFactoryTest extends TestCase
{

    public function testGetDocBlock(): void
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
}
