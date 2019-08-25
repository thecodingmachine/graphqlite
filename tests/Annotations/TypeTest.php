<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface;
use TheCodingMachine\GraphQLite\GraphQLException;

class TypeTest extends TestCase
{
    public function testException(): void
    {
        $type = new Type([]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty class for @Type annotation. You MUST create the Type annotation object using the GraphQLite AnnotationReader');
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
        $this->expectException(GraphQLException::class);
        $this->expectExceptionMessage('Problem in annotation @Type for interface "TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface": you cannot use the default="false" attribute on interfaces');
        $type->setClass(FooInterface::class);
    }

    public function testException3()
    {
        $type = new Type(['disableInheritance'=>true]);
        $this->expectException(GraphQLException::class);
        $this->expectExceptionMessage('Problem in annotation @Type for interface "TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface": you cannot use the disableInheritance="true" attribute on interfaces');
        $type->setClass(FooInterface::class);
    }
}
