<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class TypeTest extends TestCase
{
    public function testException(): void
    {
        $type = new Type([]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty class for #[Type] attribute. You MUST create the Type attribute object using the GraphQLite AnnotationReader');
        $type->getClass();
    }

    public function testExternal(): void
    {
        $type = new Type(['external'=>true]);
        $this->assertSame(false, $type->isSelfType());
    }

    public function testException2()
    {
        $type = new Type(['default'=>false]);
        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('Problem in attribute #[Type] for interface "TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface": you cannot use the default="false" attribute on interfaces');
        $type->setClass(FooInterface::class);
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $type = new Type([]);
        $this->assertNull($type->getDescription());
    }

    public function testDescriptionFromConstructor(): void
    {
        $type = new Type([], description: 'Explicit description');
        $this->assertSame('Explicit description', $type->getDescription());
    }

    public function testDescriptionFromAttributesArray(): void
    {
        $type = new Type(['description' => 'From attributes']);
        $this->assertSame('From attributes', $type->getDescription());
    }

    public function testDescriptionPreservesEmptyString(): void
    {
        // An empty string is a deliberate "explicit empty" signal that suppresses the docblock
        // fallback further down the pipeline; it must round-trip unchanged through the attribute.
        $type = new Type([], description: '');
        $this->assertSame('', $type->getDescription());
    }
}
