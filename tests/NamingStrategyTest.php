<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class NamingStrategyTest extends TestCase
{

    public function testGetInputTypeName(): void
    {
        $namingStrategy = new NamingStrategy();

        $factory = new Factory();
        $this->assertSame('FooClassInput', $namingStrategy->getInputTypeName('Bar\\FooClass', $factory));

        $factory = new Factory(['name'=>'MyInputType']);
        $this->assertSame('MyInputType', $namingStrategy->getInputTypeName('Bar\\FooClass', $factory));
    }

    public function testGetFieldNameFromMethodName(): void
    {
        $namingStrategy = new NamingStrategy();

        $this->assertSame('name', $namingStrategy->getFieldNameFromMethodName('getName'));
        $this->assertSame('get', $namingStrategy->getFieldNameFromMethodName('get'));
        $this->assertSame('name', $namingStrategy->getFieldNameFromMethodName('isName'));
        $this->assertSame('is', $namingStrategy->getFieldNameFromMethodName('is'));
        $this->assertSame('foo', $namingStrategy->getFieldNameFromMethodName('foo'));
    }
}
