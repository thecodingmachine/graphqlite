<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Annotations\Query;

class TestControllerWithInvalidParameterAnnotation
{
    /**
     * @Query()
     * @HideParameter(for="id")
     */
    public function test(string $foo = 'bar'): string
    {
        return 'foo';
    }
}
