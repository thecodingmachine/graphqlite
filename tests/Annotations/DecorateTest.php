<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DecorateTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The #[Decorate] attribute must be passed an input type. For instance: "#[Decorate("MyInputType")]"');
        new Decorate([]);
    }

    public function testPhp8Annotation(): void
    {
        $method = new ReflectionMethod(__CLASS__, 'method1');
        $attribute = $method->getAttributes()[0]->newInstance();
        $this->assertSame('foobar', $attribute->getInputTypeName());
    }

    #[Decorate("foobar")]
    public function method1(): void {
    }
}
