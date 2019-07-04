<?php

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;

class ResolveUtilsTest extends TestCase
{
    public function testAssertNull(): void
    {
        $this->expectException(TypeMismatchException::class);
        ResolveUtils::assertInnerReturnType(null, Type::nonNull(Type::string()));
    }

    public function testAssertList(): void
    {
        $this->expectException(TypeMismatchException::class);
        ResolveUtils::assertInnerReturnType(12, Type::nonNull(Type::listOf(Type::string())));
    }
}
