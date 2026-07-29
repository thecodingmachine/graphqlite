<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;

/**
 * A parameterized rule.
 *
 * Attribute arguments are constant expressions, so a callable written in an attribute cannot
 * capture or partially apply on any PHP version. `new` in an attribute argument can, and unlike a
 * loose array of extra arguments the constructor call is checked by static analysis.
 */
final class SecretIs
{
    public function __construct(private readonly string $expected)
    {
    }

    public function __invoke(SecurityRuleContext $context): bool
    {
        return $context->argument('secret') === $this->expected;
    }
}
