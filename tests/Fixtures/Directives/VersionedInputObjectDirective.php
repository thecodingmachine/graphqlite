<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/**
 * A custom input-object directive with a constructor argument. Metadata only (no apply method),
 * used to exercise the `INPUT_OBJECT` path: definition, argument encoding, and SDL output.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class VersionedInputObjectDirective implements InputObjectTypeDirective
{
    public function __construct(public readonly int $version)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'versioned',
            locations: [DirectiveLocation::INPUT_OBJECT],
        );
    }
}
