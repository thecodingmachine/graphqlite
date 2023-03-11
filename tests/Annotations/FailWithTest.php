<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class FailWithTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @FailWith annotation must be passed a defaultValue. For instance: "@FailWith(null)"');
        new FailWith([]);
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8Annotation(): void
    {
        $method = new ReflectionMethod(__CLASS__, 'method1');
        $failWith = $method->getAttributes()[0]->newInstance();
        $this->assertNull($failWith->getValue());
    }

    #[FailWith(value: null)]
    public function method1(): void
    {
    }
}
