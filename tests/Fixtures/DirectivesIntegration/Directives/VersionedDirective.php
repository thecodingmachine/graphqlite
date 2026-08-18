<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/** Input-object directive tagging a schema version; runs alongside the bundled `#[OneOf]`. */
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
