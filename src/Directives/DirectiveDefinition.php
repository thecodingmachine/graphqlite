<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * The declarative metadata for a custom directive — name, valid locations, whether it can be
 * repeated, and an optional human-readable description.
 *
 * Returned by a directive class's static {@see DirectiveInterface::definition()} method. This
 * object is deliberately binding-agnostic: it does not know whether the directive comes from a
 * PHP attribute (type-system case, this branch) or from a runtime handler (executable case,
 * future work). Argument types are not declared here — they are reflected from the directive
 * class's constructor signature when the directive is registered with the schema.
 *
 * Set {@see $builtIn} to true for attributes that bind PHP behavior to a directive already
 * provided by webonyx (`@skip`, `@include`, `@deprecated`, `@oneOf`). Built-in directives still
 * run their apply hook, but the registry does not contribute a duplicate definition to
 * {@see \GraphQL\Type\SchemaConfig::$directives} — webonyx is already the source of truth for
 * their schema-level declaration.
 */
final class DirectiveDefinition
{
    /** @param list<DirectiveLocation> $locations */
    public function __construct(
        public readonly string $name,
        public readonly array $locations,
        public readonly bool $repeatable = false,
        public readonly string|null $description = null,
        public readonly bool $builtIn = false,
    ) {
    }
}
