<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use RuntimeException;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;

class ArgumentResolverTest extends AbstractQueryProviderTest
{

    public function testResolveArrayException(): void
    {
        $argumentResolver = $this->getArgumentResolver();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected GraphQL List but value passed is not an array.');
        $argumentResolver->resolve(null, 42, null, $this->createMock(ResolveInfo::class), Type::listOf(Type::string()));
    }

    public function testResolveUnexpectedInputType(): void
    {
        $argumentResolver = $this->getArgumentResolver();

        $this->expectException(RuntimeException::class);
        $argumentResolver->resolve(null, 42, null, $this->createMock(ResolveInfo::class), new class extends Type implements InputType {});
    }
}
