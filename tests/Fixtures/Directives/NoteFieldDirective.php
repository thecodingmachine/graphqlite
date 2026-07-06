<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

/**
 * A repeatable field directive carrying a single required scalar argument. Unit specimen for the
 * resolver's argument encoding and repeatable inference.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class NoteFieldDirective implements FieldDirective
{
    public function __construct(public readonly string $text)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'note',
            locations: [DirectiveLocation::FIELD_DEFINITION],
            description: 'Attaches a freeform note to a field.',
        );
    }
}
