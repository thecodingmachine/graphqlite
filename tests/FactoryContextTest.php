<?php

namespace TheCodingMachine\GraphQLite;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\Validator;

class FactoryContextTest extends AbstractQueryProvider
{
    const GLOB_TTL_SECONDS = 2;

    public function testContext(): void
    {
        $namingStrategy = new NamingStrategy();
        $container = new EmptyContainer();
        $arrayCache = new Psr16Cache(new ArrayAdapter());
        $validator = new Validator();

        $context = new FactoryContext(
            $this->getAnnotationReader(),
            $this->getTypeResolver(),
            $namingStrategy,
            $this->getTypeRegistry(),
            $this->getFieldsBuilder(),
            $this->getTypeGenerator(),
            $this->getInputTypeGenerator(),
            $this->getTypeMapper(),
            $container,
            $arrayCache,
            $validator,
            self::GLOB_TTL_SECONDS
        );

        $this->assertSame($this->getAnnotationReader(), $context->getAnnotationReader());
        $this->assertSame($this->getTypeResolver(), $context->getTypeResolver());
        $this->assertSame($namingStrategy, $context->getNamingStrategy());
        $this->assertSame($this->getTypeRegistry(), $context->getTypeRegistry());
        $this->assertSame($this->getFieldsBuilder(), $context->getFieldsBuilder());
        $this->assertSame($this->getTypeGenerator(), $context->getTypeGenerator());
        $this->assertSame($this->getInputTypeGenerator(), $context->getInputTypeGenerator());
        $this->assertSame($this->getTypeMapper(), $context->getRecursiveTypeMapper());
        $this->assertSame($container, $context->getContainer());
        $this->assertSame($arrayCache, $context->getCache());
        $this->assertSame($validator, $context->getInputTypeValidator());
        $this->assertSame(self::GLOB_TTL_SECONDS, $context->getGlobTTL());
        $this->assertNull($context->getMapTTL());
    }
}
