<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Security;

/**
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     *
     * @param mixed $subject The scope this right applies on. $subject is typically an object or a FQCN. Set $subject to "null" if the right is global.
     */
    public function isAllowed(string $right, $subject = null): bool;
}
