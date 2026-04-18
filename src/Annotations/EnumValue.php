<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * Attaches GraphQL metadata to an individual enum case.
 *
 * Applied to cases of a PHP 8.1+ native enum exposed as a GraphQL enum type, this attribute
 * provides the schema description and deprecation reason for that value without relying on
 * docblock parsing — mirroring the explicit {@see Type::$description} and
 * {@see Field::$description} pattern that the rest of the attribute system uses.
 *
 * The attribute is named after the GraphQL specification's term for an enum member ("enum
 * value", see §3.5.2 of the spec and the `__EnumValue` introspection type), which matches the
 * GraphQL-spec-mirroring naming convention of every other graphqlite attribute (`#[Type]`,
 * `#[Field]`, `#[Query]`, etc.). The underlying PHP language construct is `case`; the GraphQL
 * schema element it produces is an enum value.
 *
 * Example:
 * ```php
 * #[Type]
 * enum Genre: string
 * {
 *     #[EnumValue(description: 'Fiction works including novels and short stories.')]
 *     case Fiction = 'fiction';
 *
 *     #[EnumValue(deprecationReason: 'Use NonFiction::Essay instead.')]
 *     case Essay = 'essay';
 *
 *     case Poetry = 'poetry'; // no explicit metadata — falls back to docblock
 * }
 * ```
 *
 * Precedence rules match the rest of the description system: an explicit `description` wins
 * over any docblock summary on the case; an explicit `deprecationReason` wins over any
 * `@deprecated` tag in the case docblock. Passing an empty-string description deliberately
 * publishes an empty description and suppresses the docblock fallback at that site (see the
 * {@see \TheCodingMachine\GraphQLite\Utils\DescriptionResolver} for details).
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final class EnumValue
{
    public function __construct(
        public readonly string|null $description = null,
        public readonly string|null $deprecationReason = null,
    ) {
    }
}
