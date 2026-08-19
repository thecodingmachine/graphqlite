<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use function ctype_upper;
use function lcfirst;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Immutable set of the method-name prefixes GraphQLite treats as property accessors.
 *
 * Getter prefixes (e.g. "get", "is", "has") map read methods to output fields; setter prefixes
 * (e.g. "set") map write methods to input fields. A prefix is only stripped when it sits on a
 * camelCase boundary, i.e. the character right after it is uppercase. This keeps genuine accessors
 * like "isEnabled" -> "enabled" while leaving ordinary words such as "issue" or "hashKey" untouched.
 */
final class FieldAccessorPrefixes
{
    /**
     * @param list<string> $getters
     * @param list<string> $setters
     */
    public function __construct(
        public readonly array $getters = ['get', 'is'],
        public readonly array $setters = ['set'],
    ) {
    }

    /**
     * Strips the matching getter prefix from a read method name to derive an output field name.
     */
    public function stripGetterPrefix(string $methodName): string
    {
        return $this->strip($methodName, $this->getters);
    }

    /**
     * Strips the matching setter prefix from a write method name to derive an input field name.
     */
    public function stripSetterPrefix(string $methodName): string
    {
        return $this->strip($methodName, $this->setters);
    }

    /**
     * Whether the method name is a setter, i.e. it carries one of the configured setter prefixes on a
     * camelCase boundary. Used to decide which methods define input fields.
     */
    public function hasSetterPrefix(string $methodName): bool
    {
        return $this->strip($methodName, $this->setters) !== $methodName;
    }

    /** @param list<string> $prefixes */
    private function strip(string $methodName, array $prefixes): string
    {
        foreach ($prefixes as $prefix) {
            $length = strlen($prefix);

            // The prefix must be present and followed by at least one more character...
            if (strlen($methodName) <= $length || ! str_starts_with($methodName, $prefix)) {
                continue;
            }

            // ...and that character must be uppercase, marking a real camelCase accessor boundary.
            if (! ctype_upper($methodName[$length])) {
                continue;
            }

            return lcfirst(substr($methodName, $length));
        }

        return $methodName;
    }
}
