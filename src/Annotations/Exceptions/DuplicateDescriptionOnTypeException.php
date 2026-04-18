<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations\Exceptions;

use BadMethodCallException;

use function implode;

/**
 * Thrown when both a #[Type] attribute and one or more #[ExtendType] attributes (or multiple
 * #[ExtendType] attributes alone) declare a `description` for the same GraphQL type.
 *
 * A GraphQL type has exactly one description, so GraphQLite must be able to pick a single
 * canonical source. Rather than silently resolving the conflict via declaration order, the
 * schema builder rejects the ambiguity with a clear error listing every offending source.
 *
 * Descriptions may therefore live on the base #[Type] OR on exactly one #[ExtendType], never
 * on both, and never on more than one #[ExtendType] for the same target class.
 */
class DuplicateDescriptionOnTypeException extends BadMethodCallException
{
    /**
     * @param class-string<object> $targetClass
     * @param list<string>         $sources    Human-readable descriptions of the attribute sources
     *                                         that contributed a description (e.g. class names).
     */
    public static function forType(string $targetClass, array $sources): self
    {
        return new self(
            'A GraphQL type may only have a description declared on the #[Type] attribute OR on exactly one #[ExtendType] attribute, never more than one. '
            . 'Target type class "' . $targetClass . '" received descriptions from multiple sources: '
            . implode(', ', $sources) . '. '
            . 'Keep the description on the #[Type] attribute, or move it to at most one #[ExtendType] attribute.',
        );
    }
}
