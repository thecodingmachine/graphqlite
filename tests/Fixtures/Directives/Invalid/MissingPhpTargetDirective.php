<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/** FIELD_DEFINITION with a TARGET_CLASS-only attribute; rejected by the target check. */
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
