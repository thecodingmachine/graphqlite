<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use PHPUnit\Framework\TestCase;

final class DirectiveLocationTest extends TestCase
{
    public function testEnumExposesAllNineteenSpecLocations(): void
    {
        $this->assertCount(19, DirectiveLocation::cases());
    }

    public function testBackingValuesMatchSpecStrings(): void
    {
        $this->assertSame('FIELD_DEFINITION', DirectiveLocation::FIELD_DEFINITION->value);
        $this->assertSame('OBJECT', DirectiveLocation::OBJECT->value);
        $this->assertSame('INPUT_OBJECT', DirectiveLocation::INPUT_OBJECT->value);
        $this->assertSame('INPUT_FIELD_DEFINITION', DirectiveLocation::INPUT_FIELD_DEFINITION->value);
    }

    public function testIsExecutableClassifiesQueryDocumentLocations(): void
    {
        $this->assertTrue(DirectiveLocation::QUERY->isExecutable());
        $this->assertTrue(DirectiveLocation::FIELD->isExecutable());
        $this->assertTrue(DirectiveLocation::FRAGMENT_DEFINITION->isExecutable());
        $this->assertTrue(DirectiveLocation::INLINE_FRAGMENT->isExecutable());
        $this->assertTrue(DirectiveLocation::VARIABLE_DEFINITION->isExecutable());
    }

    public function testIsTypeSystemClassifiesSchemaLocations(): void
    {
        $this->assertTrue(DirectiveLocation::FIELD_DEFINITION->isTypeSystem());
        $this->assertTrue(DirectiveLocation::OBJECT->isTypeSystem());
        $this->assertTrue(DirectiveLocation::INPUT_OBJECT->isTypeSystem());
        $this->assertTrue(DirectiveLocation::INPUT_FIELD_DEFINITION->isTypeSystem());
        $this->assertTrue(DirectiveLocation::ENUM->isTypeSystem());
        $this->assertTrue(DirectiveLocation::SCHEMA->isTypeSystem());
    }

    public function testIsExecutableAndIsTypeSystemArePartitions(): void
    {
        foreach (DirectiveLocation::cases() as $location) {
            $this->assertNotSame($location->isExecutable(), $location->isTypeSystem(), "Location {$location->value} should be exactly one of executable/type-system.");
        }
    }
}
