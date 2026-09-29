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
            description: 'Audit log marker',
        );

        $this->assertSame('audit', $definition->name);
        $this->assertSame([DirectiveLocation::FIELD_DEFINITION, DirectiveLocation::INPUT_FIELD_DEFINITION], $definition->locations);
        $this->assertSame('Audit log marker', $definition->description);
    }

    public function testDefaultsDescriptionToNull(): void
    {
        $definition = new DirectiveDefinition(
            name: 'noop',
            locations: [DirectiveLocation::OBJECT],
        );

        $this->assertNull($definition->description);
    }
}
