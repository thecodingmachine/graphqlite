<?php

namespace TheCodingMachine\GraphQL\Controllers;

use TheCodingMachine\GraphQL\Controllers\Fixtures\TypeFoo;
use GraphQL\Type\Definition\ObjectType;

class TypeGeneratorTest extends AbstractQueryProviderTest
{
    public function testNameAndFields()
    {
        $typeGenerator = $this->getTypeGenerator();

        $type = $typeGenerator->mapAnnotatedObject(new TypeFoo(), $this->getTypeMapper());

        $this->assertSame('TestObject', $type->name);
        $type->freeze();
        $this->assertCount(1, $type->getFields());
    }

    public function testMapAnnotatedObjectException()
    {
        $typeGenerator = $this->getTypeGenerator();

        $this->expectException(MissingAnnotationException::class);
        $typeGenerator->mapAnnotatedObject(new \stdClass(), $this->getTypeMapper());
    }
}
