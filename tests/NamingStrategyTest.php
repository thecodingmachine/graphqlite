<?php

namespace TheCodingMachine\GraphQL\Controllers;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;

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
}
