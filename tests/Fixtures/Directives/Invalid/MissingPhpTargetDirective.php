<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/**
 * Declares FIELD_DEFINITION but the PHP target is TARGET_CLASS only, so the validator rejects it:
 * the PHP target has to cover the GraphQL locations.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class MissingPhpTargetDirective implements FieldDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'badTarget',
            locations: [DirectiveLocation::FIELD_DEFINITION],
        );
    }
}
