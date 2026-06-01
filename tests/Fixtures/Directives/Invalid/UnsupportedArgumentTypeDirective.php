<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use stdClass;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/**
 * Constructor parameter is a non-scalar object — should be rejected by the validator's mappable-arg
 * rule.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class UnsupportedArgumentTypeDirective implements FieldDirective
{
    public function __construct(public readonly stdClass $thing)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'unsupportedArg',
            locations: [DirectiveLocation::FIELD_DEFINITION],
        );
    }
}
