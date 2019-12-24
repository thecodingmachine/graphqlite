<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;

class QueryFieldDescriptorTest extends TestCase
{
    public function testExceptionInSetCallable(): void
    {
        $descriptor = new QueryFieldDescriptor();
        $descriptor->setCallable([$this, 'testExceptionInSetCallable']);
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->setCallable([$this, 'testExceptionInSetCallable']);
    }

    public function testExceptionInSetTargetMethodOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor();
        $descriptor->setTargetMethodOnSource('test');
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->setTargetMethodOnSource('test');
    }

    public function testExceptionInSetMagicProperty(): void
    {
        $descriptor = new QueryFieldDescriptor();
        $descriptor->setMagicProperty('test');
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->setMagicProperty('test');
    }

    public function testExceptionInGetOriginalResolver(): void
    {
        $descriptor = new QueryFieldDescriptor();
        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->getOriginalResolver();
    }
}
