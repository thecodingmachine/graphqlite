<?php


namespace TheCodingMachine\GraphQLite\Security;

interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     *
     * @return bool
     */
    public function isLogged(): bool;
}
