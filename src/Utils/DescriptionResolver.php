<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use function array_map;
use function array_unique;
use function implode;
use function rtrim;
use function str_contains;
use function trim;

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
 *
 * It also renders the metadata items appended to a description (such as `#[Cost]` or the sentence saying a
 * `T|Undefined` value may be omitted) as a Markdown list after it.
 */
final class DescriptionResolver
{
    public const UNDEFINED_ACCEPTS_NULL = 'May be omitted; null is accepted.';
    public const UNDEFINED_REFUSES_NULL = 'May be omitted; null is not accepted.';

    public function __construct(
        private readonly bool $useDocblockFallback,
        private readonly bool $useUndefinedDescriptions = true,
    )
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

    /**
     * Returns the description item saying a `T|Undefined` value may be omitted and whether null is accepted
     *
     * GraphQL types can't say "optional but not nullable". Empty when Undefined descriptions are disabled.
     *
     * @return list<string>
     */
    public function describeUndefined(bool $refusesNull): array
    {
        if (! $this->useUndefinedDescriptions) {
            return [];
        }

        return [$refusesNull ? self::UNDEFINED_REFUSES_NULL : self::UNDEFINED_ACCEPTS_NULL];
    }

    /**
     * Renders a description followed by its metadata items as a Markdown list, after a blank line
     *
     * An item the description already contains is left out, so a developer can word it themselves.
     *
     * @param list<string> $items
     */
    public static function appendItems(string|null $description, array $items): string|null
    {
        $original = rtrim($description ?? '');
        $lines = [];
        foreach (array_unique(array_map(static fn (string $item): string => trim($item), $items)) as $item) {
            if ($item === '' || str_contains($original, $item)) {
                continue;
            }

            $lines[] = '- ' . $item;
        }

        if ($lines === []) {
            return $description;
        }

        $list = implode("\n", $lines);

        return $original === '' ? $list : $original . "\n\n" . $list;
    }
}
