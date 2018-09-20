<?php

namespace TheCodingMachine\GraphQL\Controllers;

use TheCodingMachine\GraphQL\Controllers\Fixtures\TypeFoo;
use Youshido\GraphQL\Type\Object\ObjectType;

class TypeGeneratorTest extends AbstractQueryProviderTest
{
    public function testIdentityWhenMappingAlreadyAType()
    {
        $typeGenerator = new TypeGenerator($this->getRegistry());

        $type = new ObjectType(['name'=>'foo']);
        $this->assertSame($type, $typeGenerator->mapAnnotatedObject($type));
    }

    public function testNameAndFields()
    {
        $typeGenerator = new TypeGenerator($this->getRegistry());

        $type = $typeGenerator->mapAnnotatedObject(new TypeFoo());

        $this->assertSame('TestObject', $type->getName());
        $this->assertCount(1, $type->getFields());
    }

    public function testMapAnnotatedObjectException()
    {
        $typeGenerator = new TypeGenerator($this->getRegistry());

        $this->expectException(MissingAnnotationException::class);
        $typeGenerator->mapAnnotatedObject(new \stdClass());
    }
}
