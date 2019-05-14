<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

interface AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     */
    public function isAllowed(string $right) : bool;
}
