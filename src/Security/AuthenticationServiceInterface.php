<?php


namespace TheCodingMachine\GraphQL\Controllers\Security;

interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     *
     * @return bool
     */
    public function isLogged(): bool;
}
