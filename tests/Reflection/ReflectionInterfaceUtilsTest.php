<?php

namespace TheCodingMachine\GraphQLite\Tests\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Reflection\ReflectionInterfaceUtils;
use TheCodingMachine\GraphQLite\Tests\Reflection\Fixtures\SomeClass;
use TheCodingMachine\GraphQLite\Tests\Reflection\Fixtures\SomeInterface;

class ReflectionInterfaceUtilsTest extends TestCase
{
    public function testGetDirectlyImplementedInterfaces()
    {
        $interfaces = ReflectionInterfaceUtils::getDirectlyImplementedInterfaces(new ReflectionClass(SomeClass::class));
        $this->assertCount(1, $interfaces);
        $this->assertSame(SomeInterface::class, $interfaces[SomeInterface::class]->getName());
    }
}
