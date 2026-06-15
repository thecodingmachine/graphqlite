<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\BehavioralInputObjectTypeDirective;
use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputObjectTypeHandlerInterface;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * Stands in for a user-supplied replacement of the bundled `OneOf`. Same `@oneOf` name, marked
 * `builtIn: true`, so the registry uses it instead of ours.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CustomOneOfOverride implements BehavioralInputObjectTypeDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'oneOf',
            locations: [DirectiveLocation::INPUT_OBJECT],
            builtIn: true,
        );
    }

    public function applyToInputObjectType(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType
    {
        $type = $next->handle($descriptor);
        $type->isOneOf = true;
        return $type;
    }
}
