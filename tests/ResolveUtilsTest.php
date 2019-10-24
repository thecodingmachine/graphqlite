<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResolveUtilsTest extends TestCase
{
    public function testAssertNull(): void
    {
        $this->expectException(TypeMismatchRuntimeException::class);
        ResolveUtils::assertInnerReturnType(null, Type::nonNull(Type::string()));
    }

    public function testAssertList(): void
    {
        $this->expectException(TypeMismatchRuntimeException::class);
        ResolveUtils::assertInnerReturnType(12, Type::nonNull(Type::listOf(Type::string())));
    }

    public function testAssertObjectOk(): void
    {
        ResolveUtils::assertInnerReturnType(new stdClass(), new ObjectType(['name'=>'foo']));
        $this->assertTrue(true);
    }
}
