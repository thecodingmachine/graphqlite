<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/**
 * Reuses webonyx's built-in `@oneOf` directive name — should be rejected by the registry's
 * reserved-name check.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ReservedNameDirective implements InputObjectTypeDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'oneOf',
            locations: [DirectiveLocation::INPUT_OBJECT],
        );
    }
}
