<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

use function array_key_exists;

/**
 * Everything a #[Security] rule is given about the field it is guarding.
 *
 * A rule is any callable receiving this object and returning a bool. It is invoked inside the
 * resolver wrapper, which is why the field's resolved PHP argument values are available here and
 * not merely the query literals.
 *
 * This class is public API: it is the type consumers write into their rule signatures. New
 * information is added as new readonly properties or new methods so that existing rules keep
 * compiling; the constructor is called only by GraphQLite's own Security middlewares and should be
 * treated as internal.
 *
 * A rule needing nothing GraphQL-specific can type-hint {@see SecurityRuleContextInterface}
 * instead, which is this same surface expressed as a contract, so the rule can be unit tested
 * against a fake context and reused outside a field resolution.
 */
final class SecurityRuleContext implements SecurityRuleContextInterface
{
    /**
     * @param object|null $user The currently authenticated user, or null when nobody is logged in.
     * @param object|null $source The object the field is being resolved on, if there is one.
     * @param array<string, mixed> $arguments The field's resolved PHP arguments, keyed by parameter name.
     */
    public function __construct(
        public readonly object|null $user,
        public readonly object|null $source,
        public readonly array $arguments,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly AuthorizationServiceInterface $authorizationService,
    ) {
    }

    /**
     * The currently authenticated user, or null when nobody is logged in.
     *
     * The contract's equivalent of the `$user` property.
     */
    public function getUser(): object|null
    {
        return $this->user;
    }

    /**
     * The object the field is being resolved on, if there is one.
     *
     * The contract's equivalent of the `$source` property.
     */
    public function getSource(): object|null
    {
        return $this->source;
    }

    /**
     * The field's resolved PHP arguments, keyed by parameter name.
     *
     * The contract's equivalent of the `$arguments` property.
     *
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Whether the current user holds $right, optionally against a specific subject.
     *
     * The rule-facing equivalent of the `is_granted()` expression function.
     */
    public function isGranted(string $right, mixed $subject = null): bool
    {
        return $this->authorizationService->isAllowed($right, $subject);
    }

    /**
     * Whether anybody is currently logged in.
     *
     * The rule-facing equivalent of the `is_logged()` expression function.
     */
    public function isLogged(): bool
    {
        return $this->authenticationService->isLogged();
    }

    /**
     * The field argument named $name, or null when the field has no such argument.
     */
    public function argument(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Whether the field was passed an argument named $name.
     *
     * Distinguishes an argument that was genuinely null from one that does not exist, which
     * {@see argument()} cannot.
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }
}
