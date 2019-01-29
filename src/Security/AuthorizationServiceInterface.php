<?php


namespace TheCodingMachine\GraphQLite\Security;

interface AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     *
     * @param string $right
     * @return bool
     */
    public function isAllowed(string $right): bool;
}
