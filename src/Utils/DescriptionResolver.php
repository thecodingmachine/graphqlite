<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

/**
 * Resolves a GraphQL schema description from an explicit attribute value with optional
 * docblock fallback.
 *
 * Precedence (in order):
 *   1. Explicit value (including an empty string) wins and blocks any docblock fallback.
 *   2. Docblock-derived value, but only when docblock descriptions are enabled.
 *   3. Null otherwise.
 *
 * An explicit null means "the attribute did not provide a description" and falls through to
 * the docblock fallback. An explicit empty string means "the consumer deliberately chose to
 * describe this schema element with nothing" and prevents the docblock from leaking.
 *
 * The caller is responsible for extracting the docblock-derived description string using
 * whatever strategy matches the schema element (summary only, summary + description,
 * summary + description + @var tag, etc.). This class only encodes the explicit-vs-docblock
 * precedence rule so it can be applied consistently across every extraction site.
 */
final class DescriptionResolver
{
    public function __construct(private readonly bool $useDocblockFallback)
    {
    }

    /**
     * Returns whether docblock fallback is currently enabled. Useful for callers that want
     * to skip expensive docblock parsing when the result would be discarded anyway.
     */
    public function isDocblockFallbackEnabled(): bool
    {
        return $this->useDocblockFallback;
    }

    /**
     * @param string|null $explicit         Description provided explicitly via an attribute argument.
     *                                      Null means "not provided"; an empty string means "explicit empty".
     * @param string|null $docblockDerived  Description string the caller extracted from the docblock
     *                                      (or null if there was no docblock, or if docblock extraction
     *                                      yielded nothing meaningful). Ignored when docblock fallback
     *                                      is disabled.
     */
    public function resolve(string|null $explicit, string|null $docblockDerived): string|null
    {
        if ($explicit !== null) {
            return $explicit;
        }

        if (! $this->useDocblockFallback) {
            return null;
        }

        return $docblockDerived;
    }
}
