<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/**
 * Marks an input object with a schema version. A custom input-object directive used in the
 * integration test to check the custom path runs alongside the bundled `#[OneOf]`.
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
