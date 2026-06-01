<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use PHPUnit\Framework\TestCase;

final class DirectiveDefinitionTest extends TestCase
{
    public function testStoresProvidedFields(): void
    {
        $definition = new DirectiveDefinition(
            name: 'audit',
            locations: [DirectiveLocation::FIELD_DEFINITION, DirectiveLocation::INPUT_FIELD_DEFINITION],
            repeatable: true,
            description: 'Audit log marker',
        );

        $this->assertSame('audit', $definition->name);
        $this->assertSame([DirectiveLocation::FIELD_DEFINITION, DirectiveLocation::INPUT_FIELD_DEFINITION], $definition->locations);
        $this->assertTrue($definition->repeatable);
        $this->assertSame('Audit log marker', $definition->description);
    }

    public function testDefaultsRepeatableFalseAndNoDescription(): void
    {
        $definition = new DirectiveDefinition(
            name: 'noop',
            locations: [DirectiveLocation::OBJECT],
        );

        $this->assertFalse($definition->repeatable);
        $this->assertNull($definition->description);
    }
}
