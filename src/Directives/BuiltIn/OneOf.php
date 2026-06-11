<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\BuiltIn;

use Attribute;
use GraphQL\Type\Definition\Directive as WebonyxDirective;
use TheCodingMachine\GraphQLite\Directives\BehavioralInputObjectTypeDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputObjectTypeHandlerInterface;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * Binds `#[OneOf]` to webonyx's built-in `@oneOf` directive. Putting it on an `#[Input]` class sets
 * the input object's `isOneOf` flag, which makes validation require one field and gets webonyx to
 * print `@oneOf` in the SDL.
 *
 * webonyx already defines `@oneOf`, so we don't register our own definition for it
 * ({@see DirectiveDefinition::$builtIn} is `true`).
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class OneOf implements BehavioralInputObjectTypeDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: WebonyxDirective::ONE_OF_NAME,
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
