<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class RightTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Right annotation must be passed a right name. For instance: "@Right(\'my_right\')"');
        new Right([]);
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8Annotation(): void
    {
        $method = new ReflectionMethod(__CLASS__, 'method1');
        $right = $method->getAttributes()[0]->newInstance();
        $this->assertSame('foo', $right->getName());

        $method = new ReflectionMethod(__CLASS__, 'method2');
        $right = $method->getAttributes()[0]->newInstance();
        $this->assertSame('foo', $right->getName());
    }

    #[Right(name: 'foo')]
    public function method1(): void
    {
    }

    #[Right('foo')]
    public function method2(): void
    {
    }
}
