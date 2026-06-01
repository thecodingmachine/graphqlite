<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/**
 * Implements FieldDirective but declares no FIELD_DEFINITION location — should be rejected by the
 * validator's interface/location agreement rule. The empty locations list avoids tripping the PHP
 * target rule first.
 */
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
