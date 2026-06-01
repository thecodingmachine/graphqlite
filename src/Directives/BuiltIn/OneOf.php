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
 * Binds PHP code to GraphQL's built-in `@oneOf` directive (defined by webonyx). Applying
 * `#[OneOf]` to an `#[Input]` class flips the resulting input object's `isOneOf` flag, which both
 * tightens validation (exactly one field must be supplied) and makes webonyx's schema printer emit
 * `@oneOf` on the SDL element automatically.
 *
 * The directive itself does not need a custom schema-level registration — webonyx already provides
 * the canonical definition — so {@see DirectiveDefinition::$builtIn} is `true`.
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
