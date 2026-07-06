<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/**
 * A no-argument marker field directive. Unit specimen for the "no args, not repeatable" resolver
 * path; deliberately not behavioral (nothing invokes it, we only resolve it).
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class InternalFieldDirective implements FieldDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'internal',
            locations: [DirectiveLocation::FIELD_DEFINITION],
        );
    }
}
