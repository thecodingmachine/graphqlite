<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class FactoryTest extends TestCase
{

    public function testExceptionInConstruct(): void
    {
        $this->expectException(GraphQLRuntimeException::class);
        new Factory(['default'=>false]);
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $factory = new Factory();
        $this->assertNull($factory->getDescription());
    }

    public function testDescriptionFromConstructor(): void
    {
        $factory = new Factory(description: 'Factory description');
        $this->assertSame('Factory description', $factory->getDescription());
    }

    public function testDescriptionFromAttributesArray(): void
    {
        $factory = new Factory(['description' => 'From attributes']);
        $this->assertSame('From attributes', $factory->getDescription());
    }
}
