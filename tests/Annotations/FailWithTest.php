<?php

namespace TheCodingMachine\GraphQLite\Tests\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\FailWith;

class FailWithTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The #[FailWith] attribute must be passed a defaultValue. For instance: "#[FailWith(null)]"');
        new FailWith([]);
    }

    public function testPhp8Annotation(): void
    {
        $method = new ReflectionMethod(__CLASS__, 'method1');
        $failWith = $method->getAttributes()[0]->newInstance();
        $this->assertSame(null, $failWith->getValue());
    }

    #[FailWith(value: null)]
    public function method1(): void {
    }

}
