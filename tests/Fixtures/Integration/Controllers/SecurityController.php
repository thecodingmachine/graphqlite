<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use stdClass;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\User;

class SecurityController
{
    /**
     * @Query()
     * @Security("secret=='foo'", message="Wrong secret passed")
     */
    public function getSecretPhrase(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    /**
     * @Query()
     * @Security("secret=='foo'", failWith=null)
     */
    public function getNullableSecretPhrase(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    /**
     * @Query()
     * @Security("secret=='foo'")
     * @FailWith(null)
     */
    public function getNullableSecretPhrase2(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    /**
     * @Query()
     * @Security("user && user.bar == 42")
     */
    public function getSecretUsingUser(): string
    {
        return 'you can see this secret only if user.bar is set to 42';
    }

    /**
     * @Query()
     * @Security("is_granted('CAN_EDIT', user) && is_logged()")
     */
    public function getSecretUsingIsGranted(): string
    {
        return 'you can see this secret only if user has right "CAN_EDIT"';
    }

    /**
     * @Query()
     * @Security("this.isAllowed(secret)")
     */
    public function getSecretUsingThis(string $secret): string
    {
        return 'you can see this secret only if isAllowed() returns true';
    }

    /**
     * @Query()
     * @InjectUser(for="$user")
     * @param stdClass $user
     */
    public function getInjectedUser(stdClass $user): int
    {
        return $user->bar;
    }

    public function isAllowed(string $secret): bool
    {
        return $secret === '42';
    }
}
