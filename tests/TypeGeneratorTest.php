<?php

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use stdClass;
use TheCodingMachine\GraphQLite\Containers\LazyContainer;
use TheCodingMachine\GraphQLite\Fixtures\TypeFoo;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class TypeGeneratorTest extends AbstractQueryProvider
{
    private ContainerInterface$container;

    public function setUp(): void
    {
        $this->container = new LazyContainer([
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

    public function testMapAnnotatedObjectReusesTypeRegisteredReentrantlyDuringContainerGet(): void
    {
        // Reproduces the cold-registry race behind #531: instantiating the annotated object via the
        // container reentrantly resolves (and registers) this same type. mapAnnotatedObject must
        // reuse that instance rather than build a duplicate — otherwise RecursiveTypeMapper's
        // identity check throws "Cached type in registry is not the type returned by type mapper."
        // on the first request in long-lived workers (Swoole/RoadRunner/FrankenPHP).
        $typeRegistry = $this->getTypeRegistry();
        $reentrantlyRegistered = new MutableObjectType(['name' => 'TestObject', 'fields' => []], TypeFoo::class);

        $container = new LazyContainer([
            TypeFoo::class => static function () use ($typeRegistry, $reentrantlyRegistered) {
                if (! $typeRegistry->hasType('TestObject')) {
                    $typeRegistry->registerType($reentrantlyRegistered);
                }

                return new TypeFoo();
            },
        ]);

        $typeGenerator = new TypeGenerator(
            $this->getAnnotationReader(),
            new NamingStrategy(),
            $typeRegistry,
            $container,
            $this->getTypeMapper(),
            $this->getFieldsBuilder(),
        );

        $this->assertSame($reentrantlyRegistered, $typeGenerator->mapAnnotatedObject(TypeFoo::class));
    }
}
