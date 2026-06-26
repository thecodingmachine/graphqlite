<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\BuiltIn;

use Attribute;
use GraphQL\Type\Definition\Directive as WebonyxDirective;
use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Directives\BehavioralFieldDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * Binds `#[Deprecated]` to GraphQL's built-in `deprecated` directive. Putting it on a query,
 * mutation, or field method/property sets the field's deprecation reason, which webonyx prints in
 * the SDL as `deprecated(reason: ...)`.
 *
 * webonyx already declares the `deprecated` directive, so we don't register our own definition
 * ({@see DirectiveDefinition::$builtIn} is `true`). The existing docblock deprecation support is
 * untouched: a bare `#[Deprecated]` keeps the docblock reason when there is one, and passing
 * `reason:` overrides it.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class Deprecated implements BehavioralFieldDirective
{
    public function __construct(public readonly string|null $reason = null)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: WebonyxDirective::DEPRECATED_NAME,
            locations: [DirectiveLocation::FIELD_DEFINITION],
            builtIn: true,
        );
    }

    public function applyToField(QueryFieldDescriptor $descriptor, FieldHandlerInterface $next): FieldDefinition|null
    {
        // An explicit reason wins; a bare #[Deprecated] keeps an existing docblock deprecation
        // reason, falling back to webonyx's default when there's neither.
        $reason = $this->reason ?? $descriptor->getDeprecationReason() ?? WebonyxDirective::DEFAULT_DEPRECATION_REASON;

        return $next->handle($descriptor->withDeprecationReason($reason));
    }
}
