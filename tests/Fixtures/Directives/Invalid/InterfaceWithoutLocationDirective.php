<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/** FieldDirective with no FIELD_DEFINITION location; rejected by the interface/location check. */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class InterfaceWithoutLocationDirective implements FieldDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'noLocation',
            locations: [],
        );
    }
}
