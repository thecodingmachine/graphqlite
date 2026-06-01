<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Metadata for a directive: name, valid locations, whether it's repeatable, and an optional
 * description. Returned by {@see DirectiveInterface::definition()}.
 *
 * Argument types aren't listed here; they're read from the directive class's constructor when it's
 * registered.
 *
 * Set {@see $builtIn} to true when the attribute binds behavior to a directive webonyx already
 * defines (`@oneOf`, `@deprecated`, ...). Those still run their apply hook, but we don't register a
 * second definition for them since webonyx already declares them on the schema.
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
