<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/**
 * Marks an input object with a schema version. A representative custom (non-built-in) input-object
 * directive: it goes through the full pipeline (declared in SDL, applied with rendered args,
 * registered for introspection) and demonstrates that the custom path runs alongside the bundled
 * `#[OneOf]` built-in.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class VersionedDirective implements InputObjectTypeDirective
{
    public function __construct(public readonly int $version)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'versioned',
            locations: [DirectiveLocation::INPUT_OBJECT],
            description: 'Marks an input with a schema version for backwards-compat tracking.',
        );
    }
}
