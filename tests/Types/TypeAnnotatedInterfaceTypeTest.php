<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;

class TypeAnnotatedInterfaceTypeTest extends AbstractQueryProviderTest
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
