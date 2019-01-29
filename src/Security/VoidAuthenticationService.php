<?php


namespace TheCodingMachine\GraphQLite\Security;

/**
 * A fake authentication service that always returns that the current user is NOT authenticated.
 */
class VoidAuthenticationService implements AuthenticationServiceInterface
{

    /**
     * Returns true if the "current" user is logged
     *
     * @return bool
     */
    public function isLogged(): bool
    {
        return false;
    }
}
