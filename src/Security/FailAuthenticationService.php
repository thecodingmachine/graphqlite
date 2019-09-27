<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

/**
 * A fake authentication service that always returns that the current user is NOT authenticated.
 */
class FailAuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     */
    public function isLogged(): bool
    {
        throw SecurityNotImplementedException::createNoAuthenticationException();
    }

    /**
     * Returns an object representing the current logged user.
     * Can return null if the user is not logged.
     */
    public function getUser(): ?object
    {
        throw SecurityNotImplementedException::createNoAuthenticationException();
    }
}
