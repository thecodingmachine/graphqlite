<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\ObjectTypeDirective;

#[Attribute(Attribute::TARGET_CLASS)]
final class TaggedObjectTypeDirective implements ObjectTypeDirective
{
    public function __construct(public readonly string $tag)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'tagged',
            locations: [DirectiveLocation::OBJECT],
        );
    }
}
