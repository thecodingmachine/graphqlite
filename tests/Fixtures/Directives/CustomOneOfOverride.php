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
 * Simulates a user-supplied replacement for the bundled `OneOf` built-in attribute. Claims the
 * same `@oneOf` name and marks itself as `builtIn: true` so the registry treats it as the active
 * binder for that directive name.
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
