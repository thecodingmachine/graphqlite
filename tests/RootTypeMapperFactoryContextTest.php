<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Cache\SnapshotClassBoundCache;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;

class RootTypeMapperFactoryContextTest extends AbstractQueryProvider
{
    const GLOB_TTL_SECONDS = 2;

    public function testContext(): void
    {
        $namingStrategy = new NamingStrategy();
        $container = new EmptyContainer();
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $classFinder = $this->getClassFinder('namespace');
        $classFinderComputedCache = $this->getClassFinderComputedCache();
        $classBoundCache = new SnapshotClassBoundCache($arrayCache, FilesSnapshot::alwaysUnchanged());

        $context = new RootTypeMapperFactoryContext(
            $this->getAnnotationReader(),
            $this->getTypeResolver(),
            $namingStrategy,
            $this->getTypeRegistry(),
            $this->getTypeMapper(),
            $container,
            $arrayCache,
            $classFinder,
            $classFinderComputedCache,
            $classBoundCache,
        );

        $this->assertSame($this->getAnnotationReader(), $context->getAnnotationReader());
        $this->assertSame($this->getTypeResolver(), $context->getTypeResolver());
        $this->assertSame($namingStrategy, $context->getNamingStrategy());
        $this->assertSame($this->getTypeRegistry(), $context->getTypeRegistry());
        $this->assertSame($this->getTypeMapper(), $context->getRecursiveTypeMapper());
        $this->assertSame($container, $context->getContainer());
        $this->assertSame($arrayCache, $context->getCache());
        $this->assertSame($classFinder, $context->getClassFinder());
        $this->assertSame($classFinderComputedCache, $context->getClassFinderComputedCache());
        $this->assertSame($classBoundCache, $context->getClassBoundCache());
    }
}
