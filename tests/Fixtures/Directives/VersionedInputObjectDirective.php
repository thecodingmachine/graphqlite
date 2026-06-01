<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/**
 * Demonstrates a custom (non-built-in) input-object directive carrying a constructor argument.
 * Used in tests to exercise the full custom-directive pipeline for `INPUT_OBJECT`: schema-level
 * declaration, SDL application rendering, and argument encoding. Pure metadata — no apply method.
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
