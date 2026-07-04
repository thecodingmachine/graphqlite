<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use Attribute;
use ReflectionClass;

/**
 * Reads the flags from a directive class's `#[Attribute(...)]` declaration (the `TARGET_*` bits and
 * `IS_REPEATABLE`). Shared by {@see DirectiveValidator} and {@see DirectiveResolver} so neither has
 * to know how the flags are stored.
 *
 * @internal
 */
final class DirectiveReflection
{
    /** @param ReflectionClass<TypeSystemDirective> $reflection */
    public static function attributeFlags(ReflectionClass $reflection): int
    {
        $attributes = $reflection->getAttributes(Attribute::class);
        if ($attributes === []) {
            return Attribute::TARGET_ALL;
        }

        $args = $attributes[0]->getArguments();

        return $args[0] ?? $args['flags'] ?? Attribute::TARGET_ALL;
    }
}
