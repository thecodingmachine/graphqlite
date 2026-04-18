<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtendTypeTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('In attribute #[ExtendType], missing one of the compulsory parameter "class" or "name".');
        new ExtendType([]);
    }

    public function testDescriptionDefaultsToNull(): void
    {
        $extendType = new ExtendType(['name' => 'SomeType']);
        $this->assertNull($extendType->getDescription());
    }

    public function testDescriptionFromConstructor(): void
    {
        $extendType = new ExtendType(['name' => 'SomeType'], description: 'Extension description');
        $this->assertSame('Extension description', $extendType->getDescription());
    }

    public function testDescriptionFromAttributesArray(): void
    {
        $extendType = new ExtendType(['name' => 'SomeType', 'description' => 'From attributes']);
        $this->assertSame('From attributes', $extendType->getDescription());
    }
}
