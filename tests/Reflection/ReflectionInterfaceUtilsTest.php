<?php

namespace TheCodingMachine\GraphQLite\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Reflection\Fixtures\SomeClass;
use TheCodingMachine\GraphQLite\Reflection\Fixtures\SomeInterface;

class ReflectionInterfaceUtilsTest extends TestCase
{
    public function testGetDirectlyImplementedInterfaces()
    {
        $interfaces = ReflectionInterfaceUtils::getDirectlyImplementedInterfaces(new ReflectionClass(SomeClass::class));
        $this->assertCount(1, $interfaces);
        $this->assertSame(SomeInterface::class, $interfaces[SomeInterface::class]->getName());
    }
}
