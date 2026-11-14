<?php

namespace TheCodingMachine\GraphQLite\Tests\Types;

use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Tests\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedInterfaceType;

class TypeAnnotatedInterfaceTypeTest extends AbstractQueryProvider
{

    public function testResolveTypeException()
    {
        $typeAnnotatedInterfaceType = new TypeAnnotatedInterfaceType('Foo', [], $this->getTypeMapper());
        $resolveInfo = $this->createMock(ResolveInfo::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected object for resolveType. Got: "string"');

        $typeAnnotatedInterfaceType->resolveType('foo', null, $resolveInfo);
    }
}
