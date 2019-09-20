<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use Porpaginas\Arrays\ArrayResult;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
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
}
