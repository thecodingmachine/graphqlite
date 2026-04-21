<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;

class EnumValueTest extends TestCase
{
    public function testDefaults(): void
    {
        $enumValue = new EnumValue();

        $this->assertNull($enumValue->description);
        $this->assertNull($enumValue->deprecationReason);
    }

    public function testDescriptionOnly(): void
    {
        $enumValue = new EnumValue(description: 'Fiction genre.');

        $this->assertSame('Fiction genre.', $enumValue->description);
        $this->assertNull($enumValue->deprecationReason);
    }

    public function testDeprecationReasonOnly(): void
    {
        $enumValue = new EnumValue(deprecationReason: 'Use Essay instead.');

        $this->assertNull($enumValue->description);
        $this->assertSame('Use Essay instead.', $enumValue->deprecationReason);
    }

    public function testBothValues(): void
    {
        $enumValue = new EnumValue(
            description: 'Fiction works.',
            deprecationReason: 'Use a subgenre.',
        );

        $this->assertSame('Fiction works.', $enumValue->description);
        $this->assertSame('Use a subgenre.', $enumValue->deprecationReason);
    }

    public function testDescriptionPreservesEmptyString(): void
    {
        // '' is the deliberate "explicit empty" signal that blocks docblock fallback downstream.
        $enumValue = new EnumValue(description: '');

        $this->assertSame('', $enumValue->description);
    }
}
