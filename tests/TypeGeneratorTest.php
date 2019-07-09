<?php

namespace TheCodingMachine\GraphQLite;

use Mouf\Picotainer\Picotainer;
use stdClass;
use TheCodingMachine\GraphQLite\Fixtures\TypeFoo;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class TypeGeneratorTest extends AbstractQueryProviderTest
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Picotainer([
            TypeFoo::class => function() { return new TypeFoo(); },
            stdClass::class => function() { return new stdClass(); }
        ]);
    }

    public function testNameAndFields(): void
    {
        $typeGenerator = $this->getTypeGenerator();

        $type = $typeGenerator->mapAnnotatedObject(TypeFoo::class, $this->getTypeMapper(), $this->container);

        $this->assertSame('TestObject', $type->name);
        $type->freeze();
        $this->assertCount(1, $type->getFields());
    }

    public function testMapAnnotatedObjectException(): void
    {
        $typeGenerator = $this->getTypeGenerator();

        $this->expectException(MissingAnnotationException::class);
        $typeGenerator->mapAnnotatedObject(stdClass::class, $this->getTypeMapper(), $this->container);
    }

    public function testextendAnnotatedObjectException(): void
    {
        $typeGenerator = $this->getTypeGenerator();

        $type = new MutableObjectType([
            'name' => 'foo',
            'fields' => []
        ]);

        $this->expectException(MissingAnnotationException::class);
        $typeGenerator->extendAnnotatedObject(new stdClass(), $type);
    }
}
