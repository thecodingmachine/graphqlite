<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

/**
 * The contract a #[Security] rule is handed.
 *
 * A rule that type-hints this interface instead of {@see SecurityRuleContext} depends on the
 * information it is given, not on how GraphQLite happens to supply it. Two things follow. The rule
 * can be unit tested by passing a fake context, with no schema, no query and no authentication or
 * authorization service to stand up. And the rule can be called from outside a field resolution:
 * anything holding a user, a subject and a set of named values can implement this interface over
 * them and reuse the same decision.
 *
 * The user, the subject and the named values are exposed as methods rather than as properties
 * because an interface cannot declare properties before PHP 8.4, and GraphQLite supports 8.2.
 * {@see SecurityRuleContext} keeps its public readonly `$user`, `$source` and `$arguments`
 * properties alongside these accessors, so a rule written against the concrete class is unaffected.
 */
interface SecurityRuleContextInterface
{
    /** The currently authenticated user, or null when nobody is logged in. */
    public function getUser(): object|null;

    /** The object the decision is about, or null when it is about none. */
    public function getSource(): object|null;

    /**
     * The named values the rule may read, keyed by name.
     *
     * @return array<string, mixed>
     */
    public function getArguments(): array;

    /** Whether the current user holds $right, optionally against a specific subject. */
    public function isGranted(string $right, mixed $subject = null): bool;

    /** Whether anybody is currently logged in. */
    public function isLogged(): bool;

    /** The value named $name, or null when there is no such value. */
    public function argument(string $name): mixed;

    /**
     * Whether a value named $name was supplied.
     *
     * Distinguishes a value that was genuinely null from one that does not exist, which
     * {@see argument()} cannot.
     */
    public function hasArgument(string $name): bool;
}
