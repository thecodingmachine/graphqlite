<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;
use TheCodingMachine\GraphQLite\Security\SecurityRuleMessageInterface;

use function is_int;

/**
 * A rule whose constructor argument is the security boundary itself.
 *
 * Attribute arguments are constant expressions, so a callable written in an attribute cannot
 * capture or partially apply on any PHP version. Constructing the rule in the attribute can, which
 * is why a threshold like this needs no "extra arguments" array: `new PageSizeWithin(100)` carries
 * the limit, and static analysis checks the constructor call.
 *
 * It also states its own refusal message, which is the case for the contract: the limit is known
 * here and nowhere else, so no field guarded by this rule has to repeat it in a `message:`.
 */
final class PageSizeWithin implements SecurityRuleMessageInterface
{
    public function __construct(private readonly int $max)
    {
    }

    public function __invoke(SecurityRuleContext $context): bool
    {
        $requested = $context->argument('first');

        return is_int($requested) && $requested <= $this->max;
    }

    public function getRefusalMessage(): string
    {
        return 'Page size must be at most ' . $this->max . '.';
    }
}
