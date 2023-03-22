<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;

class QueryFieldDescriptorTest extends TestCase
{
    public function testExceptionInSetCallable(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            callable: [$this, 'testExceptionInSetCallable'],
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withCallable([$this, 'testExceptionInSetCallable']);
    }

    public function testExceptionInSetTargetMethodOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            targetMethodOnSource: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetMethodOnSource('test');
    }

    public function testExceptionInSetTargetPropertyOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            targetPropertyOnSource: 'test',
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetPropertyOnSource('test');
    }

    public function testExceptionInSetMagicProperty(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            magicProperty: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withMagicProperty('test');
    }

    public function testExceptionInGetOriginalResolver(): void
    {
        $descriptor = new QueryFieldDescriptor('test', Type::string());
        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->getOriginalResolver();
    }
}
