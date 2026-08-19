<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Utils\FieldAccessorPrefixes;

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
        $this->assertSame('name', $namingStrategy->getInputFieldNameFromMethodName('setName'));
        $this->assertSame('set', $namingStrategy->getInputFieldNameFromMethodName('set'));
    }

    public function testGetFieldNameFromMethodNameOnlyStripsOnCamelCaseBoundary(): void
    {
        $namingStrategy = new NamingStrategy();

        // A prefix is only stripped when the next character is uppercase (a real accessor boundary).
        $this->assertSame('enabled', $namingStrategy->getFieldNameFromMethodName('isEnabled'));
        // Ordinary words that merely start with the prefix letters are left untouched.
        $this->assertSame('issue', $namingStrategy->getFieldNameFromMethodName('issue'));
        $this->assertSame('getaway', $namingStrategy->getFieldNameFromMethodName('getaway'));
        $this->assertSame('settings', $namingStrategy->getInputFieldNameFromMethodName('settings'));
        // "has" is not a getter prefix by default, so hassers pass through unchanged.
        $this->assertSame('hasAccess', $namingStrategy->getFieldNameFromMethodName('hasAccess'));
    }

    public function testGetFieldNameFromMethodNameWithCustomPrefixes(): void
    {
        $namingStrategy = new NamingStrategy(new FieldAccessorPrefixes(getters: ['get', 'is', 'has']));

        // "has" is now a recognised getter prefix on a camelCase boundary.
        $this->assertSame('access', $namingStrategy->getFieldNameFromMethodName('hasAccess'));
        $this->assertSame('hKey', $namingStrategy->getFieldNameFromMethodName('hasHKey'));
        // ...but words that only start with the letters "has" are still left untouched.
        $this->assertSame('hashKey', $namingStrategy->getFieldNameFromMethodName('hashKey'));
    }

    public function testGetInputFieldNameFromMethodNameWithCustomSetterPrefixes(): void
    {
        $namingStrategy = new NamingStrategy(new FieldAccessorPrefixes(setters: ['assign']));

        $this->assertSame('name', $namingStrategy->getInputFieldNameFromMethodName('assignName'));
        // A word that only starts with the letters "assign" is left untouched...
        $this->assertSame('assignup', $namingStrategy->getInputFieldNameFromMethodName('assignup'));
        // ...and "set" is no longer a configured setter prefix here, so it passes through.
        $this->assertSame('setName', $namingStrategy->getInputFieldNameFromMethodName('setName'));
    }

    public function testGetFieldNameFromTypeAnnotation(): void
    {
        $namingStrategy = new NamingStrategy();

        $type = new Type(['name' => 'foo']);

        $name = $namingStrategy->getOutputTypeName(TestObject::class, $type);
        $this->assertSame('foo', $name);
    }

    public function testGetUnionTypeName(): void
    {
        $namingStrategy = new NamingStrategy();

        $typeNames = ['Some', 'Arbitrary', 'Type', 'Names'];
        $name = $namingStrategy->getUnionTypeName($typeNames);
        $this->assertSame('UnionSomeArbitraryTypeNames', $name);
    }
}
