<?php

namespace TheCodingMachine\GraphQL\Controllers;

use TheCodingMachine\GraphQL\Controllers\Fixtures\TypeFoo;
use GraphQL\Type\Definition\ObjectType;

class TypeGeneratorTest extends AbstractQueryProviderTest
{
    public function testNameAndFields()
    {
        $typeGenerator = new TypeGenerator($this->getRegistry());

        $type = $typeGenerator->mapAnnotatedObject(new TypeFoo());

        $this->assertSame('TestObject', $type->name);
        $this->assertCount(1, $type->getFields());
    }

    public function testMapAnnotatedObjectException()
    {
        $typeGenerator = new TypeGenerator($this->getRegistry());

        $this->expectException(MissingAnnotationException::class);
        $typeGenerator->mapAnnotatedObject(new \stdClass());
    }
}
