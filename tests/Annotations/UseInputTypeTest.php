<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class UseInputTypeTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @UseInputType annotation must be passed an input type. For instance: #[UseInputType("MyInputType")]');
        new UseInputType([]);
    }

    public function testException2(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @UseInputType annotation must be passed a target and an input type. For instance: #[UseInputType("MyInputType")]');
        (new UseInputType(['inputType' => 'foo']))->getTarget();
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8Annotation(): void
    {
        $attribute = new UseInputType('foo');
        $this->assertSame('foo', $attribute->getInputType());
    }
}
