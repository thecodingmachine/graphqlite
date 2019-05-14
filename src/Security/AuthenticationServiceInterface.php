<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     */
    public function isLogged() : bool;
}
