<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use stdClass;

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
            targetClass: stdClass::class,
            targetMethodOnSource: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetMethodOnSource(stdClass::class, 'test');
    }

    public function testExceptionInSetTargetPropertyOnSource(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            targetClass: stdClass::class,
            targetPropertyOnSource: 'test',
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withTargetPropertyOnSource(stdClass::class, 'test');
    }

    public function testExceptionInSetMagicProperty(): void
    {
        $descriptor = new QueryFieldDescriptor(
            name: 'test',
            type: Type::string(),
            targetClass: stdClass::class,
            magicProperty: 'test'
        );
        $descriptor->getResolver();

        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->withMagicProperty(stdClass::class, 'test');
    }

    public function testExceptionInGetOriginalResolver(): void
    {
        $descriptor = new QueryFieldDescriptor('test', Type::string());
        $this->expectException(GraphQLRuntimeException::class);
        $descriptor->getOriginalResolver();
    }
}
