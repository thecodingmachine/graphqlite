<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;

class QueryFieldDescriptorTest extends TestCase
{
    public function testExceptionInSetCallable(): void
    {
        $descriptor = new QueryFieldDescriptor(
            callable: [$this, 'testExceptionInSetCallable'],
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withCallable([$this, 'testExceptionInSetCallable']);
    }

    public function testExceptionInSetTargetMethodOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor(
            targetMethodOnSource: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetMethodOnSource('test');
    }

    public function testExceptionInSetTargetPropertyOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor(
            targetPropertyOnSource: 'test',
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetPropertyOnSource('test');
    }

    public function testExceptionInSetMagicProperty(): void
    {
        $descriptor = new QueryFieldDescriptor(
            magicProperty: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withMagicProperty('test');
    }

    public function testExceptionInGetOriginalResolver(): void
    {
        $descriptor = new QueryFieldDescriptor();
        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->getOriginalResolver();
    }
}
