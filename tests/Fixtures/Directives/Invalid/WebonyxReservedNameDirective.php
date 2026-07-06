<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/** Well-formed but claims webonyx's `@skip` name; rejected by the reserved-name check. */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class WebonyxReservedNameDirective implements FieldDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'skip',
            locations: [DirectiveLocation::FIELD_DEFINITION],
        );
    }
}
