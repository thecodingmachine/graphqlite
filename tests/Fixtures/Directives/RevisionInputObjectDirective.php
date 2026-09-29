<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;

/** Input-object directive with one arg; specimen for the `INPUT_OBJECT` path. */
#[Attribute(Attribute::TARGET_CLASS)]
final class RevisionInputObjectDirective implements InputObjectTypeDirective
{
    public function __construct(public readonly int $number)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'revision',
            locations: [DirectiveLocation::INPUT_OBJECT],
        );
    }
}
