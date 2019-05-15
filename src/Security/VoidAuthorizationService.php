<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

/**
 * A fake authorization service with a user that has no rights
 */
class VoidAuthorizationService implements AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     */
    public function isAllowed(string $right): bool
    {
        return false;
    }
}
